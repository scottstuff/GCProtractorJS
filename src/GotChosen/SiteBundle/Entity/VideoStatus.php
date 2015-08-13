<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VideoStatus
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\VideoStatusRepository")
 */
class VideoStatus
{
    
    /**
     * Video status constants.
     * 
     * 1 = ACTIVE = Verified active video.
     * 2 = FLAGGED = Reported/Flagged by a user on the site.
     * 3 = DISABLED_ADMIN = Admin manually disabled user.
     */
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="StatusType", type="integer")
     */
    private $statusType;

    /**
     * @var string
     *
     * @ORM\Column(name="StatusDescription", type="string", length=200)
     */
    private $statusDescription;

    /**
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Video", mappedBy="status")
     */
    
    private $videos;    

     /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->videos = new ArrayCollection();
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
     * Set statusType
     *
     * @param integer $statusType
     * @return VideoStatus
     */
    public function setStatusType($statusType)
    {
        $this->statusType = $statusType;
    
        return $this;
    }

    /**
     * Get statusType
     *
     * @return integer 
     */
    public function getStatusType()
    {
        return 1;//$this->statusType;
    }

    /**
     * Set statusDescription
     *
     * @param string $statusDescription
     * @return VideoStatus
     */
    public function setStatusDescription($statusDescription)
    {
        $this->statusDescription = $statusDescription;
    
        return $this;
    }

    /**
     * Get statusDescription
     *
     * @return string 
     */
    public function getStatusDescription()
    {
        return $this->statusDescription;
    }
}
