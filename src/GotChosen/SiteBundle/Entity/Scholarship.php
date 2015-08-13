<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GotChosen\Util\Enums;

/**
 * Scholarship
 *
 * @ORM\Table(name="Scholarships")
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\ScholarshipRepository")
 */
class Scholarship
{
    // scholarship types -- 40k, monthly, video. for special behaviors.
    // 40k allows sponsorship (in ScholarshipEntry), video has voting.

    const TYPE_40K = 1;
    const TYPE_MONTHLY = 2;
    const TYPE_VIDEO = 3;
    const TYPE_EVOGAMES = 4;

    static public $scholarshipTypes = [
        self::TYPE_40K => '40K Scholarship',
        self::TYPE_MONTHLY => 'Monthly Scholarship',
        self::TYPE_VIDEO => 'Video Scholarship',
        self::TYPE_EVOGAMES => 'Evolution Games',
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="idScholarships", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ScholarshipName", type="string", length=200)
     */
    private $scholarshipName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="StartDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="EndDate", type="datetime")
     */
    private $endDate;

    /**
     * @var int
     *
     * @ORM\Column(name="ScholarshipType", type="smallint")
     */
    private $scholarshipType;
    
    /**
     * @var int 
     * 
     * @ORM\Column(name="DrawingComplete", type="boolean")
     */
    private $drawingComplete;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ScholarshipEntry", mappedBy="scholarship")
     */
    private $entries;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="VideoCategory", mappedBy="scholarship")
     */
    private $categories;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Video", mappedBy="scholarship")
     */
    private $videos;

    /**
     * @param $type
     * @return Scholarship
     */
    static public function createFake($type)
    {
        Enums::assert($type, self::$scholarshipTypes);

        $ss = new Scholarship();
        $ss->id = 0;
        $ss->scholarshipType = $type;
        $ss->startDate = date_create('first day of this month')->setTime(0, 0, 0);
        $ss->endDate = date_create('last day of this month')->setTime(23, 59, 59);

        return $ss;
    }
  
    
    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->videos = new ArrayCollection();
    }

    // meh
    public function getRulesRoute()
    {
        $type = $this->getScholarshipType();
        if ( $type === self::TYPE_40K ) {
            return ['scholarship', ['tab' => 'rules']];
        } else if ( $type === self::TYPE_MONTHLY ) {
            return ['monthly_scholarship', ['tab' => 'rules']];
        } else {
            return ['video_scholarship', ['tab' => 'about']];
        }
    }

    public function is40K()
    {
        return $this->getScholarshipType() == self::TYPE_40K;
    }
    
    public function isMonthly()
    {
        return $this->getScholarshipType() == self::TYPE_MONTHLY;
    }
    
    public function isEvoGames()
    {
        return $this->getScholarshipType() == self::TYPE_EVOGAMES;
    }
    
    public function isVideo() {
        return $this->getScholarshipType() == self::TYPE_VIDEO;
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
     * Set scholarshipName
     *
     * @param string $scholarshipName
     * @return Scholarship
     */
    public function setScholarshipName($scholarshipName)
    {
        $this->scholarshipName = $scholarshipName;
    
        return $this;
    }

    /**
     * Get scholarshipName
     *
     * @return string 
     */
    public function getScholarshipName()
    {
        return $this->scholarshipName;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Scholarship
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
     * @return Scholarship
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
     * Set scholarshipType
     *
     * @param integer $scholarshipType
     * @throws \InvalidArgumentException
     * @return Scholarship
     */
    public function setScholarshipType($scholarshipType)
    {
        Enums::assert($scholarshipType, self::$scholarshipTypes);
        $this->scholarshipType = $scholarshipType;
    
        return $this;
    }

    /**
     * Get scholarshipType
     *
     * @return integer 
     */
    public function getScholarshipType()
    {
        return $this->scholarshipType;
    }
    
    /**
     * Set drawingComplete
     * 
     * @param boolean $complete
     * @return Scholarship
     */
    public function setDrawingComplete($complete)
    {
        $this->drawingComplete = $complete;
        
        return $this;
    }
    
    public function isDrawingComplete()
    {
        return $this->drawingComplete;
    }
    
    /**
     * Add entries
     *
     * @param ScholarshipEntry $entries
     * @return Scholarship
     */
    public function addEntry(ScholarshipEntry $entries)
    {
        $this->entries[] = $entries;
    
        return $this;
    }

    /**
     * Remove entries
     *
     * @param ScholarshipEntry $entries
     */
    public function removeEntry(ScholarshipEntry $entries)
    {
        $this->entries->removeElement($entries);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEntries()
    {
        return $this->entries;
    }
}