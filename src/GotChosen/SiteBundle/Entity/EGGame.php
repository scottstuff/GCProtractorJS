<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * EGGame
 *
 * @ORM\Table(name="Games",
  *            indexes={ @ORM\Index(name="StudioName", columns={"studioName"}),
 *                       @ORM\Index(name="GameName", columns={"GameName"}),
 *                       @ORM\Index(name="CreatedDate", columns={"createdDate"}),
 *                       @ORM\Index(name="LastUpdated", columns={"lastUpdated"}),
 *                       }
 * )
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\EGGameRepository")
 */
class EGGame
{
    /**
     * Game status constants.
     * 
     * ACTIVE = Active
     * NO_API_CONNECT = Not Connected to API
     * UNDER_REVIEW = Under Review
     * ADMIN_DISABLED = Admin Disabled
     * INELIGIBLE = Ineligible
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_NO_API_CONNECT = 'no_api_connect';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_ADMIN_DISABLED = 'admin_disabled';
    const STATUS_INELIGIBLE = 'ineligible';
    
    public static $status_types = [
        self::STATUS_ACTIVE => 'Active Game',
        self::STATUS_NO_API_CONNECT => 'No API Connection',
        self::STATUS_UNDER_REVIEW => 'Under Review',
        self::STATUS_ADMIN_DISABLED => 'Disabled by Admin',
        self::STATUS_INELIGIBLE => 'Ineligible for Contest'
    ];
    
    const TYPE_FLASH = 'flash';
    const TYPE_UNITY = 'unity';
    
    public static $game_types = [
        self::TYPE_FLASH => 'Flash Game',
        self::TYPE_UNITY => 'Unity Game'
    ];

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
    private $user;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="EGGameScholarships", mappedBy="game")
     */
    private $scholarships;

    /**
     * @var string
     *
     * @ORM\Column(name="secretKey", type="string", length=40)
     */
    private $secretKey;

    /**
     * @var string
     *
     * @ORM\Column(name="studioName", type="string", length=100)
     */
    private $studioName;

    /**
     * @var string
     *
     * @ORM\Column(name="studioProfile", type="string", length=500)
     */
    private $studioProfile;

    /**
     * @var string
     *
     * @ORM\Column(name="gameName", type="string", length=100)
     */
    private $gameName;

    /**
     * @var string
     *
     * @ORM\Column(name="gameSynopsis", type="string", length=500)
     */
    private $gameSynopsis;
    
    /**
     * @var EGGameGenre
     * @ORM\ManyToOne(targetEntity="EGGameGenre", inversedBy="games")
     */
    private $genre;

    /**
     * @var string
     *
     * @ORM\Column(name="screenshotFile", type="string", length=255, nullable=true)
     */
    private $screenshotFile;
    
    /**
     * @var string
     *
     * @ORM\Column(name="avatarFile", type="string", length=255, nullable=true)
     */
    private $avatarFile;

    /**
     * @var string
     *
     * @ORM\Column(name="swfFile", type="string", length=255, nullable=true)
     */
    private $swfFile;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="type", type="string", length=50, nullable=true)
     */
    protected $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalVotes", type="integer")
     */
    private $totalVotes;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalPlays", type="integer")
     */
    private $totalPlays;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalRatedFeedbacks", type="integer")
     */
    private $totalRatedFeedbacks;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastUpdated", type="datetime")
     */
    private $lastUpdated;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="status", type="string", length=50)
     */
    protected $status;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EGGameStats", mappedBy="game")
     */
    private $statistics;

    public function __construct()
    {
        $this->totalPlays = 0;
        $this->totalVotes = 0;
        $this->totalRatedFeedbacks = 0;
        
        $random = new SecureRandom();
        $this->secretKey = bin2hex($random->nextBytes(16));
        $this->status = EGGame::STATUS_NO_API_CONNECT;
        
        $this->statistics = new ArrayCollection();
        $this->scholarships = new ArrayCollection();
        $this->createdDate = new \DateTime('now');
        $this->lastUpdated = new \DateTime('now');
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
     * Set secret key
     *
     * @param string $secretKey
     * @return EGGame
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    
        return $this;
    }

    /**
     * Get secret key
     *
     * @return string 
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set studioName
     *
     * @param string $studioName
     * @return EGGame
     */
    public function setStudioName($studioName)
    {
        $this->studioName = $studioName;
    
        return $this;
    }

    /**
     * Get studioName
     *
     * @return string 
     */
    public function getStudioName()
    {
        return $this->studioName;
    }

    /**
     * Set studioProfile
     *
     * @param string $studioProfile
     * @return EGGame
     */
    public function setStudioProfile($studioProfile)
    {
        $this->studioProfile = $studioProfile;
    
        return $this;
    }

    /**
     * Get studioProfile
     *
     * @return string 
     */
    public function getStudioProfile()
    {
        return $this->studioProfile;
    }

    /**
     * Set gameName
     *
     * @param string $gameName
     * @return EGGame
     */
    public function setGameName($gameName)
    {
        $this->gameName = $gameName;
    
        return $this;
    }

    /**
     * Get gameName
     *
     * @return string 
     */
    public function getGameName()
    {
        return $this->gameName;
    }

    /**
     * Set gameSynopsis
     *
     * @param string $gameSynopsis
     * @return EGGame
     */
    public function setGameSynopsis($gameSynopsis)
    {
        $this->gameSynopsis = $gameSynopsis;
    
        return $this;
    }

    /**
     * Get gameSynopsis
     *
     * @return string 
     */
    public function getGameSynopsis()
    {
        return $this->gameSynopsis;
    }
    
    /**
     * Set genre
     * 
     * @param EGGameGenre $genre
     * @return EGGame
     */
    public function setGenre(EGGameGenre $genre)
    {
        $this->genre = $genre;
        
        return $this;
    }
    
    /**
     * Get genre
     * 
     * @return EGGameGenre
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set screenshotFile
     *
     * @param string $screenshotFile
     * @return EGGame
     */
    public function setScreenshotFile($screenshotFile)
    {
        $this->screenshotFile = $screenshotFile;
    
        return $this;
    }

    /**
     * Get screenshotFile
     *
     * @return string 
     */
    public function getScreenshotFile()
    {
        return $this->screenshotFile;
    }
    
    /**
     * Set avatarFile
     *
     * @param string $avatarFile
     * @return EGGame
     */
    public function setAvatarFile($avatarFile)
    {
        $this->avatarFile = $avatarFile;
    
        return $this;
    }

    /**
     * Get avatarFile
     *
     * @return string 
     */
    public function getAvatarFile()
    {
        return $this->avatarFile;
    }

    /**
     * Set swfFile
     *
     * @param string $swfFile
     * @return EGGame
     */
    public function setSwfFile($swfFile)
    {
        $this->swfFile = $swfFile;
    
        return $this;
    }

    /**
     * Get swfFile
     *
     * @return string 
     */
    public function getSwfFile()
    {
        return $this->swfFile;
    }
    
    /**
     * Set type
     *
     * @param string $type
     * @return EGGame
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get isInChampionship
     *
     * @return boolean 
     */
    public function isInChampionship()
    {
        $now = new \DateTime('now');
        
        foreach ( $this->scholarships as $egScholarship ) {
            if ( $egScholarship->getScholarship()->getStartDate() <= $now
                    and $egScholarship->getScholarship()->getEndDate() >= $now 
                    and $egScholarship->getScholarshipType() == EGGameScholarships::TYPE_CHAMPIONSHIP ) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get isInQualifier
     *
     * @return boolean 
     */
    public function isInQualifier()
    {
        $now = new \DateTime('now');
        
        foreach ( $this->scholarships as $egScholarship ) {
            if ( $egScholarship->getScholarship()->getStartDate() <= $now
                    and $egScholarship->getScholarship()->getEndDate() >= $now ) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get isInContest
     *
     * @return boolean 
     */
    public function isInContest()
    {
        $now = new \DateTime('now');
        
        foreach ( $this->scholarships as $egScholarship ) {
            if ( $egScholarship->getScholarship()->getStartDate() <= $now
                    and $egScholarship->getScholarship()->getEndDate() >= $now 
                    and $egScholarship->getScholarshipType() == EGGameScholarships::TYPE_CONTEST ) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getPlaySessionPhase()
    {
        if ( $this->isInQualifier() ) {
            return EGPlaySession::PHASE_QUALIFIER;
        }
        else if ( $this->isInChampionship() ) {
            return EGPlaySession::PHASE_CHAMPIONSHIP;
        }
        else if ( $this->isInContest() ) {
            return EGPlaySession::PHASE_CONTEST;
        }
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return EGGame
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
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     * @return EGGame
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
     * Set user
     *
     * @param User $user
     * @return EGGame
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add scholarship
     *
     * @param EGGameScholarships $scholarship
     * @return EGGame
     */
    public function addScholarship(EGGameScholarships $scholarship)
    {
        if ( !$this->scholarships->contains($scholarship) ) {
            $this->scholarships->add($scholarship);
        }
    
        return $this;
    }

    /**
     * Remove scholarship
     *
     * @param EGGameScholarships $scholarship
     * @return EGGame
     */
    public function removeScholarship(EGGameScholarships $scholarship)
    {
        if ( $this->scholarships->contains($scholarship) ) {
            $this->scholarships->removeElement($scholarship);
        }
        
        return $this;
    }
    
    public function hasScholarship(EGGameScholarships $scholarship)
    {
        return $this->scholarships->contains($scholarship);
    }

    /**
     * Get scholarship
     *
     * @return ArrayCollection
     */
    public function getScholarships()
    {
        return $this->scholarships;
    }

    /**
     * Set totalVotes
     *
     * @param integer $totalVotes
     * @return EGGame
     */
    public function setTotalVotes($totalVotes)
    {
        $this->totalVotes = $totalVotes;
    
        return $this;
    }

    /**
     * Get totalVotes
     *
     * @return integer 
     */
    public function getTotalVotes()
    {
        return $this->totalVotes;
    }

    /**
     * Set totalPlays
     *
     * @param integer $totalPlays
     * @return EGGame
     */
    public function setTotalPlays($totalPlays)
    {
        $this->totalPlays = $totalPlays;
    
        return $this;
    }

    /**
     * Get totalPlays
     *
     * @return integer 
     */
    public function getTotalPlays()
    {
        return $this->totalPlays;
    }

    /**
     * Set totalRatedFeedbacks
     *
     * @param integer $totalRatedFeedbacks
     * @return EGGame
     */
    public function setTotalRatedFeedbacks($totalRatedFeedbacks)
    {
        $this->totalRatedFeedbacks = $totalRatedFeedbacks;
    
        return $this;
    }

    /**
     * Get totalRatedFeedbacks
     *
     * @return integer 
     */
    public function getTotalRatedFeedbacks()
    {
        return $this->totalRatedFeedbacks;
    }
    
    /**
     * Set status
     * 
     * @param string $status
     * @return EGGame
     */
    public function setStatus($status)
    {
        $this->status = $status;
        
        return $this;
    }
    
    /**
     * Get status/
     * 
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add statistics
     *
     * @param EGGameStats $statistics
     * @return EGGame
     */
    public function addStatistic(EGGameStats $statistics)
    {
        $this->statistics[] = $statistics;
    
        return $this;
    }

    /**
     * Remove statistics
     *
     * @param EGGameStats $statistics
     */
    public function removeStatistic(EGGameStats $statistics)
    {
        $this->statistics->removeElement($statistics);
    }

    /**
     * Get statistics
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStatistics()
    {
        return $this->statistics;
    }
}