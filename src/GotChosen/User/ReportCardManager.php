<?php

namespace GotChosen\User;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Entity\EGPlaySession;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\EGGameResultRepository;
use GotChosen\SiteBundle\Repository\EGPlayerStatsRepository;

class ReportCardManager
{
    private $cache;

    /** @var ObjectManager */
    private $em;

    public function __construct(Registry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->cache = new ArrayCache();
        $this->cache->setNamespace('eg_cards');
    }

    /**
     * @param User $user
     * @return ReportCard
     */
    public function getForUser(User $user)
    {
        $uid = (string) $user->getId();
        if ( $this->cache->contains($uid) ) {
            return $this->cache->fetch($uid);
        }

        /** @var EGPlayerStatsRepository $statsRepo */
        $statsRepo = $this->em->getRepository('GotChosenSiteBundle:EGPlayerStats');
        /** @var EGGameResultRepository $resultsRepo */
        $resultsRepo = $this->em->getRepository('GotChosenSiteBundle:EGGameResult');
        /** @var Scholarship $egScholarship */
        $egScholarship = $this->em->getRepository('GotChosenSiteBundle:Scholarship')->getCurrentEvoGames();

        $playerStats = $statsRepo->getOrCreate($user, $egScholarship, date('Y-m'));

        $card = new ReportCard();
        $card->rank = $playerStats->getRank();
        $card->maxRank = $statsRepo->getTotalPlayers(date('Y-m'));

        $card->qualifierPlays = $statsRepo->getTotalPlaySessions($user, $egScholarship, EGPlaySession::PHASE_QUALIFIER);
        $card->contestPlays = $statsRepo->getTotalPlaySessions($user, $egScholarship, EGPlaySession::PHASE_CONTEST);
        $card->wins = $resultsRepo->getTotalWins($user, date('Y-m'));
        $card->feedbacksRated = min(20, $playerStats->getFeedbacksRated());

        $card->pointsWinning = $playerStats->getGameplayPoints();
        $card->pointsBonus = $playerStats->getBonusPoints();
        $card->pointsFeedback = $playerStats->getFeedbackPoints();
        $card->pointsTotal = $playerStats->getTotalPoints();

        $this->cache->save($uid, $card);
        return $card;
    }
}
