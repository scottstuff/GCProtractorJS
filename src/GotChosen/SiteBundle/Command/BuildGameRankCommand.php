<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Repository\EGGameStatsRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildGameRankCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:build-game-rank')
            ->setDescription('Hourly job to process game rankings.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var EGGameStatsRepository $statsRepo */
        $statsRepo = $em->getRepository('GotChosenSiteBundle:EGGameStats');

        //$month = '2014-01';
        $month = date('Y-m');

        $statsRepo->buildQualifierRanking($month);
        $statsRepo->buildDevContestRanking($month);
    }
}