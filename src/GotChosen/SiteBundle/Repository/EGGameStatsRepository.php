<?php

namespace GotChosen\SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use GotChosen\SiteBundle\Entity\EGGame;
use GotChosen\SiteBundle\Entity\EGGameScholarships;
use GotChosen\SiteBundle\Entity\EGGameStats;
use GotChosen\Util\Dates;

class EGGameStatsRepository extends EntityRepository
{
    public function getOrCreate(EGGame $game, $month)
    {
        $this->assertMonth($month);

        $stats = $this->findOneBy([
            'game' => $game->getId(),
            'statsMonth' => $month,
        ]);

        if ( $stats !== null ) {
            return $stats;
        }

        $stats = new EGGameStats();
        $stats
            ->setGame($game)
            ->setStatsMonth($month)
            ->setRank(0)
            ->setMonthPlays(0)
            ->setMonthRatedFeedbacks(0)
            ->setMonthVotes(0)
            ->setLastUpdated(new \DateTime());

        $this->getEntityManager()->persist($stats);
        return $stats;
    }

    public function buildQualifierRanking($month)
    {
        $this->assertMonth($month);

        $em = $this->getEntityManager();
        $egScholarship = $em->getRepository('GotChosenSiteBundle:Scholarship')->getCurrentEvoGames();
        $scholarshipId = $egScholarship ? $egScholarship->getId() : 0;

        $conn = $em->getConnection();
        $stmt = $conn->prepare(
            'SELECT s.id FROM EGGameStats s
             LEFT JOIN game_scholarships gs ON (gs.game_id = s.game_id)
             WHERE s.statsMonth = :month AND (gs.scholarship_id IS NULL OR gs.scholarship_id != :sship)
             ORDER BY s.monthVotes DESC, s.monthPlays DESC, s.monthRatedFeedbacks DESC'
        );

        $stmt->bindValue('month', $month, \PDO::PARAM_STR);
        $stmt->bindValue('sship', $scholarshipId, \PDO::PARAM_INT);
        $stmt->execute();

        $rank = 0;
        $mapping = [];
        while ( $row = $stmt->fetch() ) {
            $mapping[$row['id']] = ++$rank;
        }

        $this->updateRankings($mapping);
    }

    public function buildDevContestRanking($month)
    {
        /*
         * - Plays
         * - Feedbacks Rated
         * - Qualifier Position
         */

        $this->assertMonth($month);

        $qualifierMonth = Dates::prevMonth($month);

        $em = $this->getEntityManager();
        $egScholarship = $em->getRepository('GotChosenSiteBundle:Scholarship')->getCurrentEvoGames();
        $scholarshipId = $egScholarship ? $egScholarship->getId() : 0;

        $conn = $em->getConnection();

        // commenting this out and sorting in PHP, might be possible to use pure MySQL, but confusing.
        // would need to make sure the previous data we're selecting is not part of a scholarship and so on.

        // possibly remove IFNULL and change sprev LEFT JOIN to normal JOIN to fully exclude
        /*$stmt = $conn->prepare(
            'SELECT s.id, IFNULL(sprev.rank, 10000) previous_rank FROM EGGameStats s
             JOIN game_scholarships gs ON (gs.game_id = s.game_id)
             LEFT JOIN EGGameStats sprev ON
                 (sprev.game_id = s.game_id AND sprev.statsMonth = :prevMonth)
             WHERE s.statsMonth = :month AND gs.scholarship_id = :sship AND gs.scholarship_type = :stype
             ORDER BY s.monthPlays DESC, s.monthRatedFeedbacks DESC, previous_rank ASC'
        );*/

        $stmt = $conn->prepare(
            'SELECT s.id, s.game_id, s.monthPlays, s.monthRatedFeedbacks
             FROM EGGameStats s
             JOIN game_scholarships gs ON (gs.game_id = s.game_id)
             WHERE s.statsMonth = :month AND gs.scholarship_id = :sship AND gs.scholarshipType = :stype'
        );

        $stmt->bindValue('month', $month, \PDO::PARAM_STR);
        //$stmt->bindValue('prevMonth', $qualifierMonth, \PDO::PARAM_STR);
        $stmt->bindValue('sship', $scholarshipId, \PDO::PARAM_INT);
        $stmt->bindValue('stype', EGGameScholarships::TYPE_CONTEST, \PDO::PARAM_STR);
        $stmt->execute();

        $values = $stmt->fetchAll();

        // collect the ranking for the previous month, so that can be used if necessary

        /*$stmt = $conn->prepare(
            'SELECT s.game_id FROM EGGameStats s
             LEFT JOIN game_scholarships gs ON (gs.game_id = s.game_id)
             WHERE s.statsMonth = :month AND (gs.scholarship_id IS NULL OR gs.scholarship_id != :sship)
             ORDER BY s.monthVotes DESC, s.monthPlays DESC, s.monthRatedFeedbacks DESC'
        );*/
        $stmt = $conn->prepare(
            'SELECT s.game_id FROM EGGameStats s
             WHERE s.statsMonth = :month
             ORDER BY s.monthVotes DESC, s.monthPlays DESC, s.monthRatedFeedbacks DESC'
        );
        $stmt->bindValue('month', $qualifierMonth, \PDO::PARAM_STR);
        //$stmt->bindValue('sship', $scholarshipId, \PDO::PARAM_INT);
        $stmt->execute();

        $qualValues = [];
        $rank = 0;
        while ( $row = $stmt->fetch() ) {
            $qualValues[$row['game_id']] = ++$rank;
        }

        // sort by monthPlays DESC, monthRatedFeedbacks DESC, and qualifier rank ASC
        usort($values, function($a, $b) use ($qualValues) {
            if ( $a['monthPlays'] != $b['monthPlays'] ) {
                return $b['monthPlays'] - $a['monthPlays']; // DESC
            }
            if ( $a['monthRatedFeedbacks'] != $b['monthRatedFeedbacks'] ) {
                return $b['monthRatedFeedbacks'] - $a['monthRatedFeedbacks']; // DESC
            }

            if ( !isset($qualValues[$a['game_id']]) ) {
                $qualValues[$a['game_id']] = 100000;
            }
            if ( !isset($qualValues[$b['game_id']]) ) {
                $qualValues[$b['game_id']] = 100000;
            }

            return $qualValues[$a['game_id']] - $qualValues[$b['game_id']];
        });

        $rank = 0;
        $mapping = [];
        foreach ( $values as $stats ) {
            $mapping[$stats['id']] = ++$rank;
        }

        $this->updateRankings($mapping);
    }

    /**
     * Efficiently update game stats rankings given an array of id => rank.
     *
     * @param $rankMap
     */
    private function updateRankings($rankMap)
    {
        $conn = $this->getEntityManager()->getConnection();

        $bindId = 0;
        $bindRank = 0;

        $stmt = $conn->prepare('UPDATE EGGameStats SET rank = :rank WHERE id = :id');
        $stmt->bindParam('id', $bindId, \PDO::PARAM_INT);
        $stmt->bindParam('rank', $bindRank, \PDO::PARAM_INT);

        foreach ( $rankMap as $id => $rank ) {
            $bindId = $id;
            $bindRank = $rank;
            $stmt->execute();
        }
    }

    private function assertMonth($month)
    {
        if ( !preg_match('/^\d{4}-\d{2}$/', $month) ) {
            throw new \InvalidArgumentException('Month must be YYYY-MM');
        }
    }
}