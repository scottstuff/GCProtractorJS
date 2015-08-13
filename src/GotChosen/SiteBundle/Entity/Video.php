<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Video
 *
 * @ORM\Table(indexes={
 *            @ORM\Index(name="VideoTitle", columns={"title"}),
 *            @ORM\Index(name="DTAdded", columns={"dtAdded"})
 *           }
 * )
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\VideoRepository")
 */
class Video
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
     * @ORM\Column(name="title", type="string", length=200)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="youtubeURL", type="string", length=500)
     */
    private $youtubeURL;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dtAdded", type="datetime")
     */
    private $dtAdded;

    /**
     *  @var VideoCategories
     * 	 MANY SIDE
     * @ORM\ManyToOne(targetEntity="VideoCategory", inversedBy="videos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;
    
    /**
     *  @var VideoStatus
     * 	 MANY SIDE
     * @ORM\ManyToOne(targetEntity="VideoStatus", inversedBy="videos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

        /**
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="VideoVote", mappedBy="video")
     */
    
    private $votes;
    
    /**
     *  @var User
     * 	 MANY SIDE
     * @ORM\ManyToOne(targetEntity="User", inversedBy="videos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;    
        
    /**
     *  @var Scholarship
     * 	 MANY SIDE
     * @ORM\ManyToOne(targetEntity="Scholarship", inversedBy="videos")
     * @ORM\JoinColumn(name="scholarship_id", referencedColumnName="idScholarships", nullable=false)
     */
    private $scholarship;     
    
    private $views;
    
    private $votesremaining;
    
     /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->dtAdded = new \DateTime('now');
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
     * Set Scholarship
     *
     * @param Scholarship $scholarship
     * @return Video
     */
    public function setScholarship($scholarship)
    {
        $this->scholarship = $scholarship;
    
        return $this;
    }

    /**
     * Get Scholarship
     *
     * @return Scholarship
     */
    public function getScholarship()
    {
        return $this->scholarship;
    }        
    
    
    /**
     * Set title
     *
     * @param string $title
     * @return Video
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set Category
     *
     * @param VideoCategories $category
     * @return Video
     */
    public function setCategory($category)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get Category
     *
     * @return VideoCategory
     */
    public function getCategory()
    {
        return $this->category;
    }    

    /**
     * Set youtubeURL
     *
     * @param string $youtubeURL
     * @return Video
     */
    public function setYoutubeURL($youtubeURL)
    {
        $this->youtubeURL = $youtubeURL;
    
        return $this;
    }

    /**
     * Get youtubeURL
     *
     * @return string 
     */
    public function getYoutubeURL()
    {
        return $this->youtubeURL;
    }

    /**
     * Set Status
     *
     * @param VideoStatus $status
     * @return Video
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get Status
     *
     * @return VideoStatus
     */
    public function getStatus()
    {
        return $this->status;
    }      

    /**
     * Set dtAdded
     *
     * @param \DateTime $dtAdded
     * @return Video
     */
    public function setDtAdded($dtAdded)
    {
        $this->dtAdded = $dtAdded;
    
        return $this;
    }

    /**
     * Get dtAdded
     *
     * @return \DateTime 
     */
    public function getDtAdded()
    {
        return $this->dtAdded;
    }
    
    /**
     * Set User
     *
     * @param User $user
     * @return Video
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get User
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }      
    
     /**
     * Get VoteCount
     *
     * @return integer
     */
    public function getVoteCount()
    {
        return count($this->votes);
    } 
    
    public function getViews()
    {
        return $this->views;
    }
    
    public function setViews($views)
    {
            $this->views = $views;
    }
    
    public function getVotesRemaining()
    {
        return $this->votesremaining;
    }
    
    public function setVotesRemaining($votesremaining)
    {
            $this->votesremaining = $votesremaining;
    }    
}
