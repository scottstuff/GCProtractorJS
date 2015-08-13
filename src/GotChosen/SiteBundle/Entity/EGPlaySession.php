<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use GotChosen\Util\Enums;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

/**
 * EGPlaySession
 *
 * @ORM\Table(name="GamePlaySession")
 * @ORM\Entity
 */
class EGPlaySession
{
    const PHASE_FREEPLAY = 0;
    const PHASE_QUALIFIER = 1;
    const PHASE_CONTEST = 2;
    const PHASE_CHAMPIONSHIP = 3;

    public static $phases = [
        self::PHASE_FREEPLAY => 'Free Play',
        self::PHASE_QUALIFIER => 'Qualifier',
        self::PHASE_CONTEST => 'Contest',
        self::PHASE_CHAMPIONSHIP => 'Championship',
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="string", length=40)
     * @ORM\Id
     */
    private $id;

    /**
     * @var EGGame
     * @ORM\ManyToOne(targetEntity="EGGame")
     */
    private $game;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $player;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isCompleted", type="boolean")
     */
    private $isCompleted;

    /**
     * @var integer
     *
     * @ORM\Column(name="phase", type="smallint")
     */
    private $phase;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var float
     *
     * @ORM\Column(name="score", type="float")
     */
    private $score;

    /**
     * @param SecureRandomInterface $random From security.secure_random
     * @param $phase
     * @param EGGame $game
     * @param User $player
     * @return EGPlaySession
     */
    public static function make(SecureRandomInterface $random, $phase, EGGame $game, User $player = null)
    {
        $session = new EGPlaySession();
        $session->setGame($game);
        $session->setPlayer($player);
        $session->setPhase($phase);

        $session->setStartDate(new \DateTime('now'));
        $session->setIsCompleted(false);
        $session->setScore(0);

        $session->id = bin2hex($random->nextBytes(20));

        return $session;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isCompleted
     *
     * @param boolean $isCompleted
     * @return EGPlaySession
     */
    public function setIsCompleted($isCompleted)
    {
        $this->isCompleted = $isCompleted;
    
        return $this;
    }

    /**
     * Get isCompleted
     *
     * @return boolean 
     */
    public function getIsCompleted()
    {
        return $this->isCompleted;
    }

    /**
     * Set phase
     *
     * @param integer $phase
     * @return EGPlaySession
     */
    public function setPhase($phase)
    {
        Enums::assert($phase, self::$phases);

        $this->phase = $phase;
    
        return $this;
    }

    /**
     * Get phase
     *
     * @return integer 
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return EGPlaySession
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return EGPlaySession
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    
        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set score
     *
     * @param float $score
     * @return EGPlaySession
     */
    public function setScore($score)
    {
        $this->score = $score;
    
        return $this;
    }

    /**
     * Get score
     *
     * @return float 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set game
     *
     * @param EGGame $game
     * @return EGPlaySession
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
     * Set player
     *
     * @param User $player
     * @return EGPlaySession
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
}