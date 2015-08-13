<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ScholarshipEntry
 *
 * @ORM\Table(name="Entries", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="UniqueIndexStoU", columns={"idScholarship", "idUser"})
 * })
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\ScholarshipEntryRepository")
 */
class ScholarshipEntry
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
     * @var Scholarship
     *
     * @ORM\ManyToOne(targetEntity="Scholarship", inversedBy="entries")
     * @ORM\JoinColumn(name="idScholarship", referencedColumnName="idScholarships")
     */
    private $scholarship;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="scholarshipEntries")
     * @ORM\JoinColumn(name="idUser", referencedColumnName="id")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="CreatedDate", type="datetime")
     */
    private $createdDate;

    // commented pending addition of an EntrySponsor { entry, sponsor, createdDate } mapping

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="EntrySponsor", mappedBy="entry")
     */
    private $sponsors;

    /**
     * @var string
     *
     * @ORM\Column(name="extraData", type="string", length=255)
     */
    private $extra; // youtube URL for videos, etc.?


    static public function make(Scholarship $scholarship, User $user)
    {
        $entry = new ScholarshipEntry();
        $entry->setScholarship($scholarship);
        $entry->setUser($user);
        return $entry;
    }

    public function __construct()
    {
        $this->createdDate = new \DateTime('now');
        $this->sponsors = new ArrayCollection();
        $this->extra = '';
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
     * @return ScholarshipEntry
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
     * Set scholarship
     *
     * @param Scholarship $scholarship
     * @return ScholarshipEntry
     */
    public function setScholarship(Scholarship $scholarship = null)
    {
        $this->scholarship = $scholarship;
    
        return $this;
    }

    /**
     * Get scholarship
     *
     * @return Scholarship
     */
    public function getScholarship()
    {
        return $this->scholarship;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return ScholarshipEntry
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
     * Set extra
     *
     * @param string $extra
     * @return ScholarshipEntry
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    
        return $this;
    }

    /**
     * Get extra
     *
     * @return string 
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Add sponsors
     *
     * @param EntrySponsor $sponsors
     * @return ScholarshipEntry
     */
    public function addSponsor(EntrySponsor $sponsors)
    {
        $this->sponsors[] = $sponsors;
    
        return $this;
    }

    /**
     * Remove sponsors
     *
     * @param EntrySponsor $sponsors
     */
    public function removeSponsor(EntrySponsor $sponsors)
    {
        $this->sponsors->removeElement($sponsors);
    }

    /**
     * Get sponsors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSponsors()
    {
        return $this->sponsors;
    }
}