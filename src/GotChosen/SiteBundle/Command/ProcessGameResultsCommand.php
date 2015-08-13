<?php

namespace GotChosen\SiteBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use GotChosen\SiteBundle\Entity\EGGame;
use GotChosen\SiteBundle\Entity\EGPlayerStats;
use GotChosen\SiteBundle\Entity\EGPlaySession;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\EGGameRepository;
use GotChosen\SiteBundle\Repository\EGPlayerStatsRepository;
use GotChosen\SiteBundle\Repository\ScholarshipRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessGameResultsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gotchosen:process-game-results')
            ->addOption('process')
            ->setDescription('Nightly job to process the day\'s game results, calculate wins/losses, '
                . 'and update percentiles.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
            Atrophius: They can get an unlimited number of wins per day.
            Atrophius: Those are restricted by token count.
            Atrophius: So, if they have 20 tokens, that's 20 possible points for a single day. In wins.
            Atrophius: Players can get up to 10 points (since there's only 10 games in the contest)
                       per day for winning different games.
            Atrophius: So, they basically get an extra point per game the first time they win it, that day.
            Steven Harris: Okay, so up to 20/day for wins, 10/day for different, from bonus points.
                           So I guess that means I also need to change calculation to allow for users
                           to win the same game 20 times?
         */

        $doProcess = $input->getOption('process');
        $yesterday = new \DateTime('yesterday');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var EGGameRepository $gameRepo */
        $gameRepo = $em->getRepository('GotChosenSiteBundle:EGGame');
        /** @var ScholarshipRepository $ssRepo */
        $ssRepo = $em->getRepository('GotChosenSiteBundle:Scholarship');

        $scholarship = $ssRepo->getCurrentEvoGames(false);
        if ( $scholarship === null ) {
            $output->writeln('No EvolutionGames scholarship is running');
            return;
        }

        $points = [];
        $winMap = [];

        /** @var EGGame[] $games */
        $games = $gameRepo->findContestGames($scholarship);
        foreach ( $games as $game ) {
            $gameId = $game->getId();
            $winMap[$gameId] = [];
            $seen = [];

            /** @var array of "[user, score, isWin, percentile]" $players */
            $players = $gameRepo->getContestPlaysForDay($game, EGPlaySession::PHASE_CONTEST, $yesterday);
            foreach ( $players as $record ) {
                /** @var User $player */
                $player = $record['user'];
                $userId = $player->getId();

                if ( !isset($points[$userId]) ) {
                    $points[$userId] = [
                        'user' => $player,
                        'gamePoints' => 0,
                        'diffPoints' => 0,
                        'percentile' => 0,
                    ];
                }

                if ( !isset($winMap[$gameId][$userId]) ) {
                    $winMap[$gameId][$userId] = ['wins' => 0, 'losses' => 0];
                }

                if ( $record['isWin'] ) {
                    // for the user's first win on this game, give them one point for "different games".
                    // limited to 10 points, but since only 10 games are in the contest, it doesn't need
                    // to be checked here.
                    if ( !isset($seen[$userId]) ) {
                        $points[$userId]['diffPoints']++;
                        $seen[$userId] = true;
                    }

                    $points[$userId]['gamePoints']++;
                    $points[$userId]['percentile'] += $record['percentile'];

                    $winMap[$gameId][$userId]['wins']++;
                } else {
                    $winMap[$gameId][$userId]['losses']++;
                }
            }
        }

        /** @var EGPlayerStatsRepository $playerStats */
        $playerStats = $em->getRepository('GotChosenSiteBundle:EGPlayerStats');

        foreach ( $points as $userId => $data ) {
            $user = $data['user'];
            $gamePoints = $data['gamePoints'];
            $diffPoints = $data['diffPoints'];
            $totalPoints = $gamePoints + $diffPoints;
            $percentileSum = $data['percentile'];
            $output->writeln(
                "User #$userId: Points = $gamePoints + $diffPoints ($totalPoints), Percentile = $percentileSum");

            if ( $doProcess ) {
                if ( $totalPoints > 0 || $percentileSum > 0 ) {
                    $stats = $playerStats->getOrCreate($user, $scholarship, $yesterday->format('Y-m'));
                    $stats->setGameplayPoints($stats->getGameplayPoints() + $gamePoints);
                    $stats->setBonusPoints($stats->getBonusPoints() + $diffPoints);
                    $stats->updateTotalPoints();
                    $stats->setTotalPercentile($stats->getTotalPercentile() + $percentileSum);
                    $stats->setLastUpdated(new \DateTime());
                }
                $em->flush();
            }
        }

        $this->processWinMap($em, $yesterday->format('Y-m'), $winMap, $output, $doProcess);
    }

    protected function processWinMap(EntityManager $em, $month, $winMap, OutputInterface $output, $doProcess)
    {
        $conn = $em->getConnection();

        $conn->beginTransaction();

        $gwinStmt = $conn->prepare('UPDATE User SET totalWins = totalWins + :wins WHERE id = :user');
        $gloseStmt = $conn->prepare('UPDATE User SET totalLosses = totalLosses + :losses WHERE id = :user');

        $winStmt = $conn->prepare(
            'UPDATE EGGameResults
             SET wins = wins + :wins
             WHERE game_id = :game AND user_id = :user AND statsMonth = :month');

        $loseStmt = $conn->prepare(
            'UPDATE EGGameResults
             SET losses = losses + :losses
             WHERE game_id = :game AND user_id = :user AND statsMonth = :month');

        $gameId = 0;
        $userId = 0;
        $wins = 0;
        $losses = 0;

        $gwinStmt->bindParam('user', $userId);
        $gwinStmt->bindParam('wins', $wins);
        $gloseStmt->bindParam('user', $userId);
        $gloseStmt->bindParam('losses', $losses);

        $winStmt->bindParam('game', $gameId, \PDO::PARAM_INT);
        $winStmt->bindParam('user', $userId, \PDO::PARAM_INT);
        $winStmt->bindParam('month', $month, \PDO::PARAM_STR);
        $winStmt->bindParam('wins', $wins, \PDO::PARAM_INT);

        $loseStmt->bindParam('game', $gameId, \PDO::PARAM_INT);
        $loseStmt->bindParam('user', $userId, \PDO::PARAM_INT);
        $loseStmt->bindParam('month', $month, \PDO::PARAM_STR);
        $loseStmt->bindParam('losses', $losses, \PDO::PARAM_INT);

        foreach ( $winMap as $g => $users ) {
            foreach ( $users as $u => $wl ) {
                $gameId = $g;
                $userId = $u;
                $wins = $wl['wins'];
                $losses = $wl['losses'];

                if ( $wins > 0 ) {
                    $output->writeln(" WIN: User #$userId, Game #$gameId, Month $month, Wins $wins");
                    if ( $doProcess ) {
                        $winStmt->execute();
                        $gwinStmt->execute();
                    }
                }

                if ( $losses > 0 ) {
                    $output->writeln("LOSE: User #$userId, Game #$gameId, Month $month, Losses $losses");
                    if ( $doProcess ) {
                        $loseStmt->execute();
                        $gloseStmt->execute();
                    }
                }
            }
        }

        $conn->commit();
    }
}