<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EGPlayerStats
 *
 * @ORM\Table(name="EGPlayerStats")
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\EGPlayerStatsRepository")
 */
class EGPlayerStats
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $player;

    /**
     * @var Scholarship
     * @ORM\ManyToOne(targetEntity="Scholarship")
     * @ORM\JoinColumn(referencedColumnName="idScholarships")
     */
    private $scholarship;

    /**
     * @var integer
     *
     * @ORM\Column(name="statsMonth", type="string", length=10)
     */
    private $statsMonth;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hasWonMonthly", type="boolean")
     */
    private $hasWonMonthly;

    /**
     * @var integer
     *
     * @ORM\Column(name="feedbacksRated", type="smallint")
     */
    private $feedbacksRated;

    /**
     * @var integer
     *
     * @ORM\Column(name="feedbackPoints", type="smallint")
     */
    private $feedbackPoints;

    /**
     * @var integer
     *
     * @ORM\Column(name="gameplayPoints", type="smallint")
     */
    private $gameplayPoints;

    /**
     * @var integer
     *
     * @ORM\Column(name="bonusPoints", type="smallint")
     */
    private $bonusPoints;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalPoints", type="smallint")
     */
    private $totalPoints;

    /**
     * @var float
     *
     * @ORM\Column(name="totalPercentile", type="float")
     */
    private $totalPercentile;

    /**
     * @var integer
     *
     * @ORM\Column(name="rank", type="integer")
     */
    private $rank;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastUpdated", type="datetime")
     */
    private $lastUpdated;


    public function updateTotalPoints()
    {
        $this->setTotalPoints($this->getFeedbackPoints() + $this->getGameplayPoints() + $this->getBonusPoints());
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set statsMonth
     *
     * @param string $statsMonth
     * @return EGPlayerStats
     */
    public function setStatsMonth($statsMonth)
    {
        $this->statsMonth = $statsMonth;
    
        return $this;
    }

    /**
     * Get statsMonth
     *
     * @return string 
     */
    public function getStatsMonth()
    {
        return $this->statsMonth;
    }

    /**
     * Set hasWonMonthly
     *
     * @param boolean $hasWonMonthly
     * @return EGPlayerStats
     */
    public function setHasWonMonthly($hasWonMonthly)
    {
        $this->hasWonMonthly = $hasWonMonthly;
    
        return $this;
    }

    /**
     * Get hasWonMonthly
     *
     * @return boolean 
     */
    public function getHasWonMonthly()
    {
        return $this->hasWonMonthly;
    }

    /**
     * Set feedbackPoints
     *
     * @param integer $feedbackPoints
     * @return EGPlayerStats
     */
    public function setFeedbackPoints($feedbackPoints)
    {
        $this->feedbackPoints = $feedbackPoints;
    
        return $this;
    }

    /**
     * Get feedbackPoints
     *
     * @return integer 
     */
    public function getFeedbackPoints()
    {
        return $this->feedbackPoints;
    }

    /**
     * Set gameplayPoints
     *
     * @param integer $gameplayPoints
     * @return EGPlayerStats
     */
    public function setGameplayPoints($gameplayPoints)
    {
        $this->gameplayPoints = $gameplayPoints;
    
        return $this;
    }

    /**
     * Get gameplayPoints
     *
     * @return integer 
     */
    public function getGameplayPoints()
    {
        return $this->gameplayPoints;
    }

    /**
     * Set totalPoints
     *
     * @param integer $totalPoints
     * @return EGPlayerStats
     */
    public function setTotalPoints($totalPoints)
    {
        $this->totalPoints = $totalPoints;
    
        return $this;
    }

    /**
     * Get totalPoints
     *
     * @return integer 
     */
    public function getTotalPoints()
    {
        return $this->totalPoints;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     * @return EGPlayerStats
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    
        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime 
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Set player
     *
     * @param User $player
     * @return EGPlayerStats
     */
    public function setPlayer(User $player = null)
    {
        $this->player = $player;
    
        return $this;
    }

    /**
     * Get player
     *
     * @return User
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Set scholarship
     *
     * @param Scholarship $scholarship
     * @return EGPlayerStats
     */
    public function setScholarship(Scholarship $scholarship = null)
    {
        $this->scholarship = $scholarship;
    
        return $this;
    }

    /**
     * Get scholarship
     *
     * @return Scholarship
     */
    public function getScholarship()
    {
        return $this->scholarship;
    }

    /**
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     * @return $this
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalPercentile()
    {
        return $this->totalPercentile;
    }

    /**
     * @param float $totalPercentile
     * @return $this
     */
    public function setTotalPercentile($totalPercentile)
    {
        $this->totalPercentile = $totalPercentile;
        return $this;
    }

    /**
     * @return int
     */
    public function getFeedbacksRated()
    {
        return $this->feedbacksRated;
    }

    /**
     * @param int $feedbacksRated
     * @return $this
     */
    public function setFeedbacksRated($feedbacksRated)
    {
        $this->feedbacksRated = $feedbacksRated;
        return $this;
    }

    /**
     * @return int
     */
    public function getBonusPoints()
    {
        return $this->bonusPoints;
    }

    /**
     * @param int $bonusPoints
     * @return $this
     */
    public function setBonusPoints($bonusPoints)
    {
        $this->bonusPoints = $bonusPoints;
        return $this;
    }
}