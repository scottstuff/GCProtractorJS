<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EGGameResult
 *
 * @ORM\Table(name="EGGameResults")
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\EGGameResultRepository")
 */
class EGGameResult
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
     * @var string
     *
     * @ORM\Column(name="statsMonth", type="string", length=10)
     */
    private $statsMonth;

    /**
     * @var integer
     *
     * @ORM\Column(name="wins", type="smallint")
     */
    private $wins;

    /**
     * @var integer
     *
     * @ORM\Column(name="losses", type="smallint")
     */
    private $losses;

    /**
     * @var integer
     *
     * @ORM\Column(name="plays", type="smallint")
     */
    private $plays;

    /**
     * @var EGGame
     *
     * @ORM\ManyToOne(targetEntity="EGGame")
     */
    private $game;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

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
     * @return EGGameResult
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
     * Set wins
     *
     * @param integer $wins
     * @return EGGameResult
     */
    public function setWins($wins)
    {
        $this->wins = $wins;
    
        return $this;
    }

    /**
     * Get wins
     *
     * @return integer 
     */
    public function getWins()
    {
        return $this->wins;
    }

    /**
     * Set losses
     *
     * @param integer $losses
     * @return EGGameResult
     */
    public function setLosses($losses)
    {
        $this->losses = $losses;
    
        return $this;
    }

    /**
     * Get losses
     *
     * @return integer 
     */
    public function getLosses()
    {
        return $this->losses;
    }

    /**
     * Set plays
     *
     * @param integer $plays
     * @return EGGameResult
     */
    public function setPlays($plays)
    {
        $this->plays = $plays;
    
        return $this;
    }

    /**
     * Get plays
     *
     * @return integer 
     */
    public function getPlays()
    {
        return $this->plays;
    }

    /**
     * @return EGGame
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param EGGame $game
     * @return $this
     */
    public function setGame($game)
    {
        $this->game = $game;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
}
