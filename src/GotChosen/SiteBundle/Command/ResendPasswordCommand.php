<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RequestContext;

class ResendPasswordCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:resend:password')
            ->addOption('send')
            ->setDescription('Resend password retrieval notices.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set translator locale, otherwise all of our translations are ignored.
        $this->getContainer()->get('translator')->setLocale('en');

        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        /** @var UserRepository $userRepo */
        $userRepo = $em->getRepository('GotChosenSiteBundle:User');

        $qb = $userRepo->createQueryBuilder('u');
        $q = $qb->where($qb->expr()->isNotNull('u.passwordRequestedAt'))
            ->andWhere($qb->expr()->isNotNull('u.confirmationToken'))
            ->andWhere($qb->expr()->length('u.password') . ' = 0')
            ->getQuery();

        $users = $q->getResult();
        if ( count($users) == 0 ) {
            $output->writeln('No users found');
            return;
        }

        $mailer = $this->getContainer()->get('mailer');
        $doSend = $input->getOption('send');
        foreach ( $users as $user ) {
            $output->writeln($user->getUsername());
            if ( $doSend ) {
                $user->setPasswordRequestedAt(new \DateTime());
                $this->getContainer()->get('fos_user.user_manager')->updateUser($user);

                $this->sendConfirmation($mailer, $user);
                $output->writeln(' - sent');
            }
        }
        $output->writeln(count($users) . ' users found');

        if ( $doSend ) {
            $output->writeln('Flushing queue...');
            $spool = $mailer->getTransport()->getSpool();
            $transport = $this->getContainer()->get('swiftmailer.transport.real');

            $spool->flushQueue($transport);
        }
    }

    protected function sendConfirmation($mailer, User $user)
    {
        $router = $this->getContainer()->get('router');
        $templating = $this->getContainer()->get('templating');

        $router->setContext(new RequestContext('', 'GET', 'www.gotchosen.com', 'https'));

        $params = ['token' => $user->getConfirmationToken()];
        $url = $router->generate('fos_user_resetting_reset', $params, true);
        $rendered = $templating->render('FOSUserBundle:Resetting:email.txt.twig', array(
            'user' => $user,
            'confirmationUrl' => $url
        ));

        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($rendered));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('noreply@gotchosen.com', 'GotChosen')
            ->setTo($user->getEmail())
            ->setBody($body);

        $mailer->send($message);

        $user->setPasswordRequestedAt(new \DateTime());
        $this->getContainer()->get('fos_user.user_manager')->updateUser($user);
    }
}