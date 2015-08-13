<?php

namespace GotChosen\SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use GotChosen\SiteBundle\Entity\EGPlayerStats;
use GotChosen\SiteBundle\Entity\EGPlaySession;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\Util\Dates;
use GotChosen\Util\Enums;

class EGPlayerStatsRepository extends EntityRepository
{
    public function getOrCreate(User $player, Scholarship $scholarship, $month)
    {
        $stats = $this->findOneBy([
            'player' => $player->getId(),
            'scholarship' => $scholarship->getId(),
            'statsMonth' => $month,
        ]);

        if ( $stats !== null ) {
            return $stats;
        }

        $stats = new EGPlayerStats();
        $stats
            ->setPlayer($player)
            ->setScholarship($scholarship)
            ->setStatsMonth($month)
            ->setRank(0)
            ->setFeedbacksRated(0)
            ->setFeedbackPoints(0)
            ->setGameplayPoints(0)
            ->setBonusPoints(0)
            ->setTotalPoints(0)
            ->setTotalPercentile(0)
            ->setRank(0)
            ->setHasWonMonthly(false)
            ->setLastUpdated(new \DateTime());

        $this->getEntityManager()->persist($stats);
        return $stats;
    }

    public function getPointSummation(User $player, Scholarship $scholarship)
    {
        /** @var EGPlayerStats[] $allStats */
        $allStats = $this->findBy([
            'player' => $player->getId(),
            'scholarship' => $scholarship->getId(),
        ]);

        $totals = [
            'feedback' => 0,
            'gameplay' => 0,
            'total' => 0,
        ];

        foreach ( $allStats as $stats ) {
            $totals['feedback'] += $stats->getFeedbackPoints();
            $totals['gameplay'] += $stats->getGameplayPoints();
            $totals['total']    += $stats->getTotalPoints();
        }

        return $totals;
    }

    /**
     * @param User $player
     * @param Scholarship $scholarship A fake scholarship, returned by getCurrentEvoGames. Used for dates.
     * @return int
     */
    public function getQualifierPlaySessions(User $player, Scholarship $scholarship)
    {
        return $this->getTotalPlaySessions($player, $scholarship, EGPlaySession::PHASE_QUALIFIER);
    }

    public function getContestPlaySessions(User $player, Scholarship $scholarship)
    {
        return $this->getTotalPlaySessions($player, $scholarship, EGPlaySession::PHASE_CONTEST);
    }

    public function getTotalPlaySessions(User $player, Scholarship $scholarship, $phase = null)
    {
        if ( $phase !== null ) {
            Enums::assert($phase, EGPlaySession::$phases);
            $phaseCond = 'AND gs.phase = :phase';
        } else {
            $phaseCond = '';
        }

        $start = $scholarship->getStartDate();
        $end   = $scholarship->getEndDate();

        $q = $this->getEntityManager()->createQuery(
            'SELECT COUNT(gs.id) FROM GotChosenSiteBundle:EGPlaySession gs
             WHERE gs.player = :user
                 AND gs.isCompleted = 1
                 AND gs.endDate >= :dateStart
                 AND gs.endDate <= :dateEnd ' . $phaseCond
        );
        $q->setParameter('user', $player->getId());
        $q->setParameter('dateStart', $start->format('Y-m-d H:i:s'));
        $q->setParameter('dateEnd', $end->format('Y-m-d H:i:s'));
        if ( $phase !== null ) {
            $q->setParameter('phase', $phase);
        }

        $count = (int) $q->getSingleScalarResult();
        return $count;
    }

    /**
     * Retrieves play sessions for the given user, optionally filtered by month.
     *
     * @param User $player
     * @param null|string $month In the form of YYYY-MM
     * @return array
     */
    public function findPlaySessions(User $player, $month = null)
    {
        $q = $this->getEntityManager()->createQueryBuilder();
        $q->select('ps', 'g')
            ->from('GotChosenSiteBundle:EGPlaySession', 'ps')
            ->join('ps.game', 'g')
            ->where('ps.player = :user')
            ->setParameter('user', $player->getId())
            ->andWhere('ps.isCompleted = 1');

        if ( $month && preg_match('/^\d{4}-\d{2}$/', $month) ) {
            $q->andWhere('ps.endDate >= :start')
                ->setParameter('start', $month . '-01 00:00:00')
                ->andWhere('ps.endDate < :end')
                ->setParameter('end', Dates::nextMonth($month) . '-01 00:00:00');
        }

        $q->orderBy('ps.endDate');

        return $q->getQuery()->getResult();
    }

    /**
     * @param $month
     */
    public function buildChampionshipRanking($month)
    {
        $this->assertMonth($month);

        $em = $this->getEntityManager();
        //$egScholarship = $em->getRepository('GotChosenSiteBundle:Scholarship')->getCurrentEvoGames();
        //$scholarshipId = $egScholarship ? $egScholarship->getId() : 0;

        $conn = $em->getConnection();
        $stmt = $conn->prepare(
            'SELECT s.id FROM EGPlayerStats s
             WHERE s.statsMonth = :month
             ORDER BY s.totalPoints DESC, s.feedbacksRated DESC, s.totalPercentile DESC'
        );

        $stmt->bindValue('month', $month, \PDO::PARAM_STR);
        //$stmt->bindValue('sship', $scholarshipId, \PDO::PARAM_INT);
        $stmt->execute();

        $rank = 0;
        $mapping = [];
        while ( $row = $stmt->fetch() ) {
            $mapping[$row['id']] = ++$rank;
        }

        $this->updateRankings($mapping);
    }

    public function getChampionshipLeaders(Scholarship $scholarship, $month, $limit = 10)
    {
        $this->assertMonth($month);
        $em = $this->getEntityManager();

        $q = $em->createQuery(
            'SELECT s, u FROM GotChosenSiteBundle:EGPlayerStats s
             JOIN s.player u
             WHERE s.scholarship = :sship AND s.statsMonth = :month
             ORDER BY s.rank'
        );
        $q->setParameter('sship', $scholarship->getId());
        $q->setParameter('month', $month);
        $q->setMaxResults($limit);

        return $q->getResult();
    }

    private function updateRankings($rankMap)
    {
        $conn = $this->getEntityManager()->getConnection();

        $bindId = 0;
        $bindRank = 0;

        $stmt = $conn->prepare('UPDATE EGPlayerStats SET rank = :rank WHERE id = :id');
        $stmt->bindParam('id', $bindId, \PDO::PARAM_INT);
        $stmt->bindParam('rank', $bindRank, \PDO::PARAM_INT);

        foreach ( $rankMap as $id => $rank ) {
            $bindId = $id;
            $bindRank = $rank;
            $stmt->execute();
        }
    }

    public function getTotalPlayers($month)
    {
        $this->assertMonth($month);

        $q = $this->getEntityManager()->createQuery(
            'SELECT COUNT(ps.id) FROM GotChosenSiteBundle:EGPlayerStats ps
             WHERE ps.statsMonth = :month'
        );
        $q->setParameter('month', $month);

        return (int) $q->getSingleScalarResult();
    }

    private function assertMonth($month)
    {
        if ( !preg_match('/^\d{4}-\d{2}$/', $month) ) {
            throw new \InvalidArgumentException('Month must be YYYY-MM');
        }
    }
}