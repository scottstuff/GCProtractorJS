<?php

namespace GotChosen\SiteBundle\Controller;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserManager;
use GotChosen\Mail\Filter;
use GotChosen\SiteBundle\Controller\BaseController;
use GotChosen\SiteBundle\Entity\MassMailQueue;
use GotChosen\SiteBundle\Entity\NotificationType;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\MassMailQueueRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MailController
 * @package GotChosen\SiteBundle\Controller
 *
 * @Route(options={"i18n" = false})
 */
class MailController extends BaseController
{
    /**
     * View a fully rendered e-mail from a MassMailQueue in a
     * browser.
     *
     * @param int $id
     *
     * @Route("/newsletter/view/{id}", name="newsletter_view")
     */
    public function viewInBrowserAction($id)
    {
        $entry = $this->repo('MassMailQueue')->find($id);

        $params = $entry->getParameters();
        $params['unsubscribe_link'] = '';

        return $this->render('GotChosenSiteBundle:Newsletters:' . $entry->getTemplate(), $params);
    }

    /**
     * Controller action for catching bounces from mailgun and doing appropriate
     * things in our DB with them.
     *
     * @param Request $request
     *
     * @Route("/bouncer/mailgun", name="bouncer_mailgun")
     */
    public function bouncerMailgunAction(Request $request)
    {
        $email = $request->request->get('recipient');

        if ( !$email ) {
            return new Response("OK");
        }

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByEmail($email);

        if ( !$user ) {
            return new Response("OK");
        }

        $user->setEnabled(false);
        $user->setStatus(User::STATUS_BAD_EMAIL);
        $this->em()->flush();

        return new Response("OK");
    }
    
    /**
     * Controller action for catching bounces from Mandrill and doing appropriate
     * things in our DB with them.
     *
     * @param Request $request
     *
     * @Route("/bouncer/mandrill", name="bouncer_mandrill")
     */
    public function bouncerMandrillAction(Request $request)
    {
        $mandrillEvents = $request->request->get('mandrill_events');
        
        if ( !$mandrillEvents ) {
            return new Response("OK");
        }
        
        $userManager = $this->get('fos_user.user_manager');
        
        $jsonData = json_decode($mandrillEvents, true);
        foreach ( $jsonData as $event ) {
            if ( !isset($event['msg']['email']) ) {
                continue;
            }
            
            $user = $userManager->findUserByEmail($email);
            
            if ( !$user ) {
                continue;
            }
            
            $user->setEnabled(false);
            $user->setStatus(User::STATUS_BAD_EMAIL);
            $this->em()->flush();
        }
        
        return new Response("OK");
    }
}
