<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntrySponsor
 *
 * @ORM\Table(name="EntrySponsors")
 * @ORM\Entity
 */
class EntrySponsor
{
    /**
     * @var ScholarshipEntry
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ScholarshipEntry", inversedBy="sponsors")
     * @ORM\JoinColumn(name="entryId", referencedColumnName="id"))
     */
    private $entry;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    public function __construct()
    {
        $this->createdDate = new \DateTime('now');
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return EntrySponsor
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
     * Set entry
     *
     * @param ScholarshipEntry $entry
     * @return EntrySponsor
     */
    public function setEntry(ScholarshipEntry $entry)
    {
        $this->entry = $entry;
    
        return $this;
    }

    /**
     * Get entry
     *
     * @return ScholarshipEntry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return EntrySponsor
     */
    public function setUser(User $user)
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
}