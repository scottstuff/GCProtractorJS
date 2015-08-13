<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EGGameStats
 *
 * @ORM\Table(name="EGGameStats")
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\EGGameStatsRepository")
 */
class EGGameStats
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
     * @var EGGame
     *
     * @ORM\ManyToOne(targetEntity="EGGame", inversedBy="statistics")
     */
    private $game;
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="rank", type="integer")
     */
    private $rank;

    /**
     * @var integer
     *
     * @ORM\Column(name="statsMonth", type="string", length=10)
     */
    private $statsMonth;

    /**
     * @var integer
     *
     * @ORM\Column(name="monthVotes", type="integer")
     */
    private $monthVotes;
    
    /**
     * @var integer
     * 
     * @ORM\Column(name="monthRatedFeedbacks", type="integer")
     */
    private $monthRatedFeedbacks;

    /**
     * @var integer
     *
     * @ORM\Column(name="monthPlays", type="integer")
     */
    private $monthPlays;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastUpdated", type="datetime")
     */
    private $lastUpdated;

    public static function make(EGGame $game, $month = '')
    {
        if ( !$month ) {
            $month = date('Y-m');
        }
        
        if ( !preg_match('/\d{4}\-\d{2}/', $month) ) {
            throw new \InvalidArgumentException("Month must be in the format 'Y-m' (e.g. 2013-12).");
        }

        $stats = new EGGameStats();
        $stats->setGame($game);
        $stats->setStatsMonth($month);

        return $stats;
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
     * @param integer $statsMonth
     * @return EGGameStats
     */
    public function setStatsMonth($statsMonth)
    {
        $this->statsMonth = $statsMonth;
    
        return $this;
    }

    /**
     * Get statsMonth
     *
     * @return integer 
     */
    public function getStatsMonth()
    {
        return $this->statsMonth;
    }

    /**
     * Set monthVotes
     *
     * @param integer $monthVotes
     * @return EGGameStats
     */
    public function setMonthVotes($monthVotes)
    {
        $this->monthVotes = $monthVotes;
    
        return $this;
    }

    /**
     * Get monthVotes
     *
     * @return integer 
     */
    public function getMonthVotes()
    {
        return $this->monthVotes;
    }

    /**
     * Set monthPlays
     *
     * @param integer $monthPlays
     * @return EGGameStats
     */
    public function setMonthPlays($monthPlays)
    {
        $this->monthPlays = $monthPlays;
    
        return $this;
    }

    /**
     * Get monthPlays
     *
     * @return integer 
     */
    public function getMonthPlays()
    {
        return $this->monthPlays;
    }
    
    /**
     * Set monthRatedFeedbacks
     * 
     * @param integer $monthRatedFeedbacks
     * @return EGGameStats
     */
    public function setMonthRatedFeedbacks($monthRatedFeedbacks)
    {
        $this->monthRatedFeedbacks = $monthRatedFeedbacks;
        
        return $this;
    }
    
    /**
     * Get monthRatedFeedbacks
     * 
     * @return integer
     */
    public function getMonthRatedFeedbacks()
    {
        return $this->monthRatedFeedbacks;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     * @return EGGameStats
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
     * Set game
     *
     * @param EGGame $game
     * @return EGGameStats
     */
    public function setGame(EGGame $game = null)
    {
        $this->game = $game;
    
        return $this;
    }

    /**
     * Get game
     *
     * @return EGGame
     */
    public function getGame()
    {
        return $this->game;
    }
    
    /**
     * Set rank
     * 
     * @param integer $rank
     * @return EGGameStats
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
        
        return $this;
    }
    
    /**
     * Get rank
     * 
     * @return integer
     */
    public function getRank()
    {
        return $this->rank;
    }
}