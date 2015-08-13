<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\ORM\EntityManager;
use GotChosen\SiteBundle\Entity\EGGame;
use GotChosen\SiteBundle\Entity\EGGameScholarships;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Repository\EGGameRepository;
use GotChosen\SiteBundle\Repository\EGGameStatsRepository;
use GotChosen\SiteBundle\Repository\ScholarshipRepository;
use GotChosen\Util\Dates;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewEGContestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:new-eg-contest')
            ->addOption('round', 'r', InputOption::VALUE_REQUIRED, 'Contest round #')
            ->addOption('month', 'm', InputOption::VALUE_OPTIONAL,
                'Month to pull qualifier games from, defined as yyyy-MM, defaults to previous month',
                Dates::prevMonth('now'))
            ->setDescription('Creates a new Evolution Games contest, seeding it with the top 10 qualifier games.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
         * Create a new EG Scholarship
         * Add Contest games based on the Top 10 by rank from qualifier games associated with statsMonth
         * Ensure new Contest games have EGGameStats entries for the contest
         */

        $round = $input->getOption('round');
        $month = $input->getOption('month');

        // VALUE_REQUIRED only means that --round with no value is unacceptable.
        // it still allows --round to be left off entirely (hence why it's called an "option")
        if ( $round === null || !is_numeric($round) ) {
            throw new \RuntimeException('round must be given and numeric');
        }

        if ( $month !== null && !preg_match('/^\d{4}-\d{2}$/', $month) ) {
            throw new \RuntimeException('month must be in yyyy-MM format');
        }

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var EGGameRepository $gameRepo */
        $gameRepo = $em->getRepository('GotChosenSiteBundle:EGGame');
        /** @var ScholarshipRepository $ssRepo */
        $ssRepo = $em->getRepository('GotChosenSiteBundle:Scholarship');
        /** @var EGGameStatsRepository $gsRepo */
        $gsRepo = $em->getRepository('GotChosenSiteBundle:EGGameStats');

        $prevContest = $ssRepo->getCurrentEvoGames();

        $title = "Evolution Games Contest: Round $round";
        $start = date_create('first day of this month')->setTime(0, 0, 0);
        $end   = date_create('last day of this month')->setTime(23, 59, 59);

        $newContest = new Scholarship();
        $newContest
            ->setScholarshipName($title)
            ->setScholarshipType(Scholarship::TYPE_EVOGAMES)
            ->setStartDate($start)
            ->setEndDate($end)
            ->setDrawingComplete(false);

        $em->persist($newContest);
        $em->flush();

        $output->writeln('Created Scholarship:');
        $output->writeln($title);
        $output->writeln('Start Date: ' . $start->format('Y-m-d H:i:s'));
        $output->writeln('End Date:   ' . $end->format('Y-m-d H:i:s'));

        $output->writeln('');
        $output->writeln('Adding Games:');

        // add games
        /** @var EGGame[] $games */
        $games = $gameRepo->findQualifierGamesByRank($prevContest, $month, 10);

        $i = 1;
        foreach ( $games as $game ) {
            $output->writeln("$i. " . $game->getGameName());
            $i++;

            // create scholarship mapping
            $mapping = EGGameScholarships::make($game, $newContest, EGGameScholarships::TYPE_CONTEST);
            $em->persist($mapping);
            $game->addScholarship($mapping);

            // add game stats record if needed
            $gsRepo->getOrCreate($game, $start->format('Y-m'));
        }

        $em->flush();
    }
}
