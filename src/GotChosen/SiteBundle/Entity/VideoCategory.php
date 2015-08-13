<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * VideoCategory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\VideoCategoryRepository")
 */
class VideoCategory
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
     * @ORM\Column(name="CategoryName", type="string", length=200)
     */
    private $categoryName;

    /**
     * @var string
     *
     * @ORM\Column(name="CategoryDescription", type="string", length=500)
     */
    private $categoryDescription;

    /**
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Video", mappedBy="category")
     */
    
    private $videos;

    /**
     *  @var Scholarship
     * 	 MANY SIDE
     * @ORM\ManyToOne(targetEntity="Scholarship", inversedBy="categories")
     * @ORM\JoinColumn(name="scholarship_id", referencedColumnName="idScholarships")
     */
    private $scholarship;       
    
     /**
     * Constructor
     */
    public function __construct()
    {
        $this->videos = new ArrayCollection();
    }    
    
    /**
     * Set Scholarship
     *
     * @param Scholarship $scholarship
     * @return VideoCategory
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set categoryName
     *
     * @param string $categoryName
     * @return VideoCategory
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;
    
        return $this;
    }

    /**
     * Get categoryName
     *
     * @return string 
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Set categoryDescription
     *
     * @param string $categoryDescription
     * @return VideoCategory
     */
    public function setCategoryDescription($categoryDescription)
    {
        $this->categoryDescription = $categoryDescription;
    
        return $this;
    }

    /**
     * Get categoryDescription
     *
     * @return string 
     */
    public function getCategoryDescription()
    {
        return $this->categoryDescription;
    }

}
