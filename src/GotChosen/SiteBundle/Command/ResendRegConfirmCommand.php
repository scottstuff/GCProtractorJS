<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\UserRepository;
use JMS\I18nRoutingBundle\Router\I18nRouter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RequestContext;

class ResendRegConfirmCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:resend:regconfirm')
            ->addOption('send')
            ->setDescription('Resend registration confirmation notices.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set translator locale, otherwise all of our translations are ignored.
        $this->getContainer()->get('translator')->setLocale('en');

        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $mailer = $this->getContainer()->get('gotchosen.mail.no_spool_mailer');
        
        $doSend = $input->getOption('send');
        
        $conn = $em->getConnection();
        
        $stmt = $conn->prepare(
            "SELECT username, email, confirmation_token FROM User
             WHERE enabled = 0 AND createdDate > ?
             AND status = 'unconfirmed'
             AND confirmation_token IS NOT NULL
             AND confirmation_token <> ''"
        );
        $stmt->bindValue(1, "2013-10-03 00:00:00");
        $stmt->execute();
        
        $count = 0;
        while ( $user = $stmt->fetch() ) {
            $count++;
            $output->writeln($user['username']);
            if ( $doSend ) {
                $this->sendConfirmation($mailer, $user);
                $output->writeln(' - sent');
            }
        }
        $output->writeln("{$count} users found");
    }
    
    protected function sendConfirmation($mailer, $user)
    {
        /** @var I18NRouter $router */
        $router = $this->getContainer()->get('router');
        $templating = $this->getContainer()->get('templating');

        $router->setContext(new RequestContext('', 'GET', 'www.gotchosen.com', 'https'));

        $params = ['token' => $user['confirmation_token']];
        $url = $router->generate('fos_user_registration_confirm', $params, true);
        $rendered = $templating->render('FOSUserBundle:Registration:email.txt.twig', array(
            'user' => $user,
            'confirmationUrl' =>  $url
        ));

        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($rendered));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('noreply@gotchosen.com', 'GotChosen')
            ->setTo($user['email'])
            ->setBody($body);
        
        // We're going to bulk send these over Mailgun instead of Mandrill
        $headers = $message->getHeaders();
        $headers->addTextHeader('X-Gc-Type', 'mass');

        $mailer->send($message);
    }
}
