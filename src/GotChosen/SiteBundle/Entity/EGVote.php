<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EGVote
 *
 * @ORM\Table(name="Votes",
 *            indexes={
 *            @ORM\Index(name="CreatedDate", columns={"createdDate"}),
 *            @ORM\Index(name="IPAddress", columns={"ipAddress"})
 *           }
 * )
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\EGVoteRepository")
 */
class EGVote
{
    const MAX_PER_DAY = 5;

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
     * @ORM\ManyToOne(targetEntity="EGGame")
     */
    private $game;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     * @var string
     *
     * @ORM\Column(name="ipAddress", type="string", length=15)
     */
    private $ipAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="sessionId", type="string", length=50)
     */
    private $sessionId;

    public static function make(EGGame $game, $ip, $session)
    {
        $vote = new EGVote();
        $vote->game = $game;
        $vote->createdDate = new \DateTime('now');
        $vote->ipAddress = $ip;
        $vote->sessionId = $session;

        return $vote;
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
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return EGVote
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    
        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime 
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     * @return EGVote
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    
        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string 
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set game
     *
     * @param EGGame $game
     * @return EGVote
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
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }
}