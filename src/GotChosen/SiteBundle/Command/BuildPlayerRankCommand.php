<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Repository\EGPlayerStatsRepository;
use GotChosen\Util\Dates;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildPlayerRankCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:build-player-rank')
            ->setDescription('Hourly job to process player rankings.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ObjectManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var EGPlayerStatsRepository $statsRepo */
        $statsRepo = $em->getRepository('GotChosenSiteBundle:EGPlayerStats');

        //$month = '2014-01';
        $month = date('Y-m');

        $statsRepo->buildChampionshipRanking($month);
        if ( date('j') == '1' ) {
            $statsRepo->buildChampionshipRanking(Dates::prevMonth($month));
        }
    }
}