<?php

namespace GotChosen\SiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use GotChosen\SiteBundle\Entity\EGGame;
use GotChosen\SiteBundle\Entity\EGVote;

class EGVoteRepository extends EntityRepository
{
    public function getVotesRemaining(EGGame $game, $ipAddress, $sessionId, \DateTime $day)
    {
        $q = $this->getEntityManager()->createQuery(
            'SELECT COUNT(v.id) FROM GotChosenSiteBundle:EGVote v
             WHERE v.game = ?1 AND v.ipAddress = ?2 AND v.createdDate BETWEEN ?3 AND ?4 AND v.sessionId = ?5'
        );
        $q->setParameter(1, $game->getId());
        $q->setParameter(2, $ipAddress);
        $q->setParameter(3, $day->format('Y-m-d 00:00:00'));
        $q->setParameter(4, $day->format('Y-m-d 23:59:59'));
        $q->setParameter(5, $sessionId);

        $sessionCount = $q->getSingleScalarResult();
        if ( $sessionCount == 0 ) {
            $q = $this->getEntityManager()->createQuery(
                'SELECT COUNT(v.id) FROM GotChosenSiteBundle:EGVote v
                 WHERE v.game = ?1 AND v.ipAddress = ?2 AND v.createdDate BETWEEN ?3 AND ?4'
            );
            $q->setParameter(1, $game->getId());
            $q->setParameter(2, $ipAddress);
            $q->setParameter(3, $day->format('Y-m-d 00:00:00'));
            $q->setParameter(4, $day->format('Y-m-d 23:59:59'));
            $dayCount = $q->getSingleScalarResult();
            return max(0, EGVote::MAX_PER_DAY - $dayCount);
        } else {
            return 0;
        }
    }

    public function hasVotedOnGame(EGGame $game, $ipAddress, $sessionId, \DateTime $day)
    {
        $remaining = $this->getVotesRemaining($game, $ipAddress, $sessionId, $day);
        return $remaining < EGVote::MAX_PER_DAY;
    }

    public function canVoteOnGame(EGGame $game, $ipAddress, $sessionId, \DateTime $day)
    {
        return $this->getVotesRemaining($game, $ipAddress, $sessionId, $day) > 0;
    }
}