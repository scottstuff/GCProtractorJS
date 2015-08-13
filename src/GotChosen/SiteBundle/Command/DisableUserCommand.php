<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:user:disable')
            ->addArgument('email')
            ->setDescription('Finds a user by e-mail address and disables them.');
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
        $q = $qb->where('u.emailCanonical = ?1')
            ->setParameter(1, strtolower($input->getArgument('email')))
            ->getQuery();
        
        $user = $q->getOneOrNullResult();
        if ( !$user ) {
            $output->writeln('User not found.');
            return;
        }
        
        if ( !$user->isEnabled() and $user->getConfirmationToken() == '' ) {
            $output->writeln('User already disabled.');
            return;
        }
        
        $mailer = $this->getContainer()->get('mailer');
        $output->writeln($user->getUsername());
        
        $user->setEnabled(false);
        $user->setConfirmationToken(null);
        $user->setStatus(User::STATUS_DISABLED_ADMIN);
        
        $em->flush();
        
        $this->sendConfirmation($mailer, $user);
        $output->writeln(' - email sent');
        
        $output->writeln('Flushing queue...');
        $spool = $mailer->getTransport()->getSpool();
        $transport = $this->getContainer()->get('swiftmailer.transport.real');

        $spool->flushQueue($transport);
    }
    
    protected function sendConfirmation($mailer, User $user)
    {
        $templating = $this->getContainer()->get('templating');

        $body = $templating->render('GotChosenSiteBundle:Emails:disabled_user.txt.twig', array(
            'user' => $user
        ));

        $message = \Swift_Message::newInstance()
            ->setSubject('GotChosen: User account for e-mail "' . $user->getEmail() . '" has been cancelled')
            ->setFrom('noreply@gotchosen.com', 'GotChosen')
            ->setTo($user->getEmail())
            ->setBody($body);

        $mailer->send($message);
    }
}
