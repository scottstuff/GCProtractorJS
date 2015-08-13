<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VideoVote
 *
 * @ORM\Table(indexes={
 *            @ORM\Index(name="DTAdded", columns={"dtAdded"}),
 *            @ORM\Index(name="ip4Address", columns={"IP4Address"})
 *           }
 * )
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\VideoVoteRepository")
 */
class VideoVote
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
     * @var string
     *
     * @ORM\Column(name="IP4Address", type="string", length=15)
     */
    private $ip4Address;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dtAdded", type="datetime")
     */
    private $dtAdded;
    
    /**
     * @var string
     *
     * * @ORM\Column(name="sessionId", type="string", length=50)
     */
    private $sessionId;    

    /**
     *  @var Video
     * 	 MANY SIDE
     * @ORM\ManyToOne(targetEntity="Video", inversedBy="votes")
     * @ORM\JoinColumn(name="video_id", referencedColumnName="id", nullable=false)
     */
    private $video;    
    
    public static function make(Video $video, $ip, $sessionId)
    {
        $vote = new VideoVote();
        $vote->video = $video;
        $vote->dtAdded = new \DateTime('now');
        $vote->ip4Address = $ip;
        $vote->sessionId = $sessionId;

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
     * Set iP4Address
     *
     * @param string $iP4Address
     * @return VideoVote
     */
    public function setIP4Address($ip4Address)
    {
        $this->ip4Address = $ip4Address;
    
        return $this;
    }

    /**
     * Get iP4Address
     *
     * @return string 
     */
    public function getIP4Address()
    {
        return $this->ip4Address;
    }

    /**
     * Set dtAdded
     *
     * @param \DateTime $dtAdded
     * @return VideoVote
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
     * Get Video
     *
     * @return Video
     */
    public function getVideo()
    {
        return $this->video;
    }       
    
    /**
     * Get sessionId
     *
     * @return string 
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }    
    /**
     * Set sessionId
     *
     * @return VideoVote 
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    
        return $this;    }    
}
