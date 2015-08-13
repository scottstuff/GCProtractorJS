<?php

namespace GotChosen\Mail;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use GotChosen\SiteBundle\Entity\MassMailQueue;
use GotChosen\Util\Strings;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

class Processor
{
    /** @var EntityManager */
    private $em;

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var EngineInterface */
    private $templateEngine;

    /** @var RouterInterface */
    private $router;

    /** @var bool */
    private $simulate = false;

    /** @var OutputInterface */
    private $debugOut;

    /** @var BatchLimiterInterface */
    private $limiter;

    public function __construct(Registry $doctrine, \Swift_Mailer $mailer,
                                EngineInterface $templateEngine, RouterInterface $router,
                                BatchLimiterInterface $limiter)
    {
        $this->em = $doctrine->getManager();
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
        $this->router = $router;
        $this->limiter = $limiter;

        $this->debugOut = new NullOutput();

        $this->router->setContext(new RequestContext('', 'GET', 'www.gotchosen.com', 'https'));
    }

    public function setOutput(OutputInterface $output)
    {
        $this->debugOut = $output;
    }

    public function enableSimulation()
    {
        $this->simulate = true;
    }

    public function process(MassMailQueue $entry, $batchSize = 100)
    {
        $this->debugOut->writeln('-----------------------------------');
        $this->debugOut->writeln("Starting entry: " . $entry->getSubject());

        $start = $entry->getPosition();
        // Don't change (reduce) our original total after the first time it's
        // set as that could cause us to not send to the complete list of users
        // we originally found with our filter
        $total = max($this->countUsers($entry), $entry->getTotal());

        $this->debugOut->writeln("Found total: $total");

        if ( !$this->simulate ) {
            $entry->setTotal($total);
            $entry->setStatus(MassMailQueue::STATUS_PROCESSING);
            $this->em->flush();
        }
        
        $complete = false;
        
        // loop through, constantly fetching from the last user + 1, until nothing left.
        // LIMIT x,y would be slow (needs to look at *all* results even before offset),
        // and prone to duplicate/missing sends if a user changes their preferences mid-process.
        while ( true ) {
            $currentDate = new \DateTime();
            $userLimit = $batchSize;

            // if not a preview, apply batch limiting
            if ( $entry->getType() !== MassMailQueue::TYPE_PREVIEW ) {
                $remaining = $this->limiter->getMessagesRemaining($currentDate);
                if ( $remaining <= 0 ) {
                    $this->debugOut->writeln('Reached batch limitations, breaking');
                    break;
                }
                $userLimit = min($userLimit, $remaining);
            }

            // allow pausing
            $this->em->refresh($entry);
            if ( $entry->getStatus() === MassMailQueue::STATUS_PAUSED ) {
                $this->debugOut->writeln('Entry paused, breaking');
                break;
            }

            $users = $this->fetchUsers($entry, $start, $userLimit);
            if ( empty($users) ) {
                $this->debugOut->writeln('No more users found, breaking loop');
                // Make sure we're not here because we're over our limit
                if ( $remaining > 0 ) {
                    $complete = true;
                }
                break;
            }

            foreach ( $users as $user ) {
                $this->debugOut->writeln(
                    "User #{$user['id']}; un={$user['username']}; em={$user['email']}; "
                    . "fn={$user['properties']['FirstName']} {$user['properties']['LastName']}");

                if ( !$this->simulate ) {
                    $html = $this->renderTemplate($entry, $user);

                    $message = \Swift_Message::newInstance($entry->getSubject(), $html, 'text/html', 'utf-8');
                    $message->setFrom('noreply@gotchosen.com');
                    try {
                        $message->setTo($user['email']);
                    }
                    catch ( \Swift_RfcComplianceException $e ) {
                        // Just skip over bad e-mail addresses
                        $this->debugOut->writeln("Skipping badly formatted e-mail address: {$user['email']}");
                        continue;
                    }

                    $headers = $message->getHeaders();
                    $headers->addTextHeader('X-Gc-Type', 'mass');
                    $unsub = $this->getUnsubscribeLink($entry, $user);
                    if ( $unsub ) {
                        $headers->addTextHeader('List-Unsubscribe',
                            '<' . $this->getUnsubscribeLink($entry, $user) . '>');
                    }

                    $this->mailer->send($message);
                }

                $start = $user['id'] + 1;
            }

            if ( !$this->simulate ) {
                $entry->setPosition($start);
                $entry->setSent($entry->getSent() + count($users));
                $this->em->flush();
            }

            if ( $entry->getType() === MassMailQueue::TYPE_PREVIEW ) {
                $complete = true;
                break;
            } else {
                $this->limiter->addMessagesSent($currentDate, count($users));
            }
        }

        if ( !$this->simulate ) {
            if ( $complete ) {
                // This is more accurate than the reverse, I think.
                $entry->setTotal($entry->getSent());
                $entry->setStatus(MassMailQueue::STATUS_COMPLETE);
            }
            $this->em->flush();
        }
    }

    /**
     * @param MassMailQueue $entry
     * @param $offset int User ID to start fetching from
     * @param $limit
     * @return array
     */
    private function fetchUsers(MassMailQueue $entry, $offset, $limit)
    {
        $query = $this->em->getConnection()->createQueryBuilder();
        $query->select('u.id', 'u.username', 'u.email')
            ->from('User', 'u');

        if ( $entry->getType() === MassMailQueue::TYPE_PREVIEW ) {
            $query->where('u.id = :uid')->setParameter('uid', $entry->getFilterSpec()['userId']);
        } else {
            $filter = Filter::fromArray($entry->getFilterSpec(), $this->em);
            $this->applyQueryFilter($query, $filter);

            $query->andWhere('u.id >= :curId')->setParameter('curId', $offset);
        }

        $query->setMaxResults($limit);
        $query->orderBy('u.id', 'ASC');

        //$this->debugOut->writeln($query->getSQL());

        $all = [];
        $res = $query->execute();
        while ( $row = $res->fetch() ) {
            $all[(int) $row['id']] = $row;
        }

        $this->addProperties($all, ['FirstName', 'LastName']);
        return $all;
    }

    private function countUsers(MassMailQueue $entry)
    {
        if ( $entry->getType() === MassMailQueue::TYPE_PREVIEW ) {
            return 1;
        }

        $query = $this->em->getConnection()->createQueryBuilder();
        $query->select('COUNT(*)')
            ->from('User', 'u');

        $filter = Filter::fromArray($entry->getFilterSpec(), $this->em);
        $this->applyQueryFilter($query, $filter);

        return $query->execute()->fetchColumn();
    }

    private function applyQueryFilter(QueryBuilder $query, Filter $filter)
    {
        $query->where('u.enabled = 1');

        if ( $filter->isEmpty() ) {
            return;
        }

        if ( $nt = $filter->getNotificationType() ) {
            // JOIN NotificationSubscriptions ns ON (u.id = ns.idUser AND ns.idNotificationType = x)
            $query->innerJoin('u', 'NotificationSubscriptions', 'ns',
                'u.id = ns.idUser AND ns.idNotificationType = :ntype');
            $query->setParameter('ntype', $nt->getId());
        }
        
        if ( $status = $filter->getUserStatus() ) {
            $query->andWhere('u.status = :status');
            $query->setParameter('status', $status);
        }

        $sslist = $filter->getScholarships();
        if ( !empty($sslist) ) {
            $ssids = [];
            foreach ( $sslist as $scholarship ) {
                $ssids[] = $scholarship->getId();
            }
            $ssids = implode(',', $ssids);
            $query->innerJoin('u', 'Entries', 'e', 'u.id = e.idUser AND e.idScholarship IN(' . $ssids . ')');
        }

        if ( class_exists('GotChosen\SiteBundle\Entity\EGGame') && $filter->hasSubmittedGame() ) {
            $query->innerJoin('u', 'Games', 'g', 'u.id = g.user_id');
        }

        if ( $lang = $filter->getLanguage() ) {
            $query->innerJoin('u', 'UserProfile', 'up',
                'u.id = up.user_id AND up.property_id = 19 AND up.propertyValue = :lang');
            $query->setParameter('lang', $lang);
        }
    }

    private function addProperties(array &$users, array $limitProps = [])
    {
        if ( empty($users) ) {
            return;
        }

        $userIds = array_keys($users);
        $propWhere = '';
        if ( !empty($limitProps) ) {
            $propWhere = 'AND p.name IN (\'' . implode("', '", $limitProps) . '\')';
        }

        // injecting user ids directly in case a large array causes doctrine's SQL parser to barf
        $q = $this->em->getConnection()->createQueryBuilder();
        $q->select('up.propertyValue', 'up.user_id', 'p.name')
            ->from('UserProfile', 'up')
            ->innerJoin('up', 'ProfileProperty', 'p', 'up.property_id = p.id')
            ->where('up.user_id IN (' . implode(',', $userIds) . ') ' . $propWhere);

        $results = $q->execute();
        while ( $result = $results->fetch() ) {
            $uid = (int) $result['user_id'];
            if ( !isset($users[$uid]['properties']) ) {
                $users[$uid]['properties'] = [];
            }
            $users[$uid]['properties'][$result['name']] = $result['propertyValue'];
        }
    }

    private function renderTemplate(MassMailQueue $entry, array $user)
    {
        $params = $entry->getParameters();
        $params['user'] = $user;
        $params['unsubscribe_link'] = $this->getUnsubscribeLink($entry, $user);
        $params['view_in_browser'] = $this->getViewInBrowserLink($entry);

        return $this->templateEngine->render('GotChosenSiteBundle:Newsletters:' . $entry->getTemplate(), $params);
    }

    private function getUnsubscribeLink(MassMailQueue $entry, array $user)
    {
        $filter = $entry->getFilterSpec();
        if ( isset($filter['notificationTypeId']) ) {
            return $this->router->generate('user_unsubscribe', [
                'type' => $filter['notificationTypeId'],
                'email' => Strings::base64EncodeUrl($user['email']),
            ], true);
        }

        return false;
    }
    
    private function getViewInBrowserLink(MassMailQueue $entry)
    {
        return $this->router->generate('newsletter_view', ['id' => $entry->getId()], true);
    }
}
