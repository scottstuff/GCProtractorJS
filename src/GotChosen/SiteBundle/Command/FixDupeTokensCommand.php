<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixDupeTokensCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:fix-tokens')
            ->setDescription('Finds users that have duplicate confirmation tokens and resets them.');
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
        $tokenGenerator = $this->getContainer()->get('fos_user.util.token_generator');
        
        $conn = $em->getConnection();
        
        $stmt = $conn->query(
            'SELECT confirmation_token FROM User
                GROUP BY confirmation_token
             HAVING COUNT(confirmation_token) > 1'
        );
        
        while ( $row = $stmt->fetch() ) {
            $users = $userRepo->findBy(['confirmationToken' => $row['confirmation_token']]);
            foreach ( $users as $user ) {
                $output->writeln('Updating confirmation token for: ' . $user->getUsername());
                
                do {
                    $token = $tokenGenerator->generateToken();
                } while ( $userRepo->findBy(['confirmationToken' => $token]) );
                
                $output->writeln("- Changing token from {$row['confirmation_token']} to $token ...");
                $user->setConfirmationToken($token);
            }
        }
        
        $em->flush();
        
        $output->writeln('Done.');
    }
}
