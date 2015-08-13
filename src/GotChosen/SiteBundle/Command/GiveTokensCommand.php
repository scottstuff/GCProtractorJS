<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GiveTokensCommand extends ContainerAwareCommand
{
    const TOKENS_PER_DAY = 5;

    protected function configure()
    {
        $this
            ->setName('gotchosen:give-tokens')
            ->setDescription('Nightly job to distribute 5 tokens to active contest participants.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var Scholarship $scholarship */
        $scholarship = $em->getRepository('GotChosenSiteBundle:Scholarship')->getCurrentEvoGames(false);

        if ( !$scholarship ) {
            $output->writeln('No EvolutionGames scholarship is running');
            return;
        }

        // just get users applied to a scholarship?
        $q = $em->createQuery(
            'SELECT u FROM GotChosenSiteBundle:User u
             JOIN u.scholarshipEntries se
             WHERE se.scholarship = :sship'
        );
        $q->setParameter('sship', $scholarship->getId());

        $users = $q->getResult();
        /** @var $user User */
        foreach ( $users as $user ) {
            echo $user->getUsername(), "\n";
            $user->setTokens($user->getTokens() + self::TOKENS_PER_DAY);
        }

        $em->flush();
    }
}