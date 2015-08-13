<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProfilePropertiesInNetworks
 *
 * @ORM\Table(name="ProfileProperties_In_Networks")
 * @ORM\Entity
 */
class ProfilePropertiesInNetworks
{
    /**
     * @var UserProfile
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UserProfile", inversedBy="visibleNetworks")
     * @ORM\JoinColumn(name="propertyId")
     */
    private $profileProperty;

    /**
     * @var UsergMeshNetwork
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UsergMeshNetwork")
     * @ORM\JoinColumn(name="networkId", referencedColumnName="networkId")
     */
    private $network;

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
     * @return ProfilePropertiesInNetworks
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
     * Set profileProperty
     *
     * @param UserProfile $profileProperty
     * @return ProfilePropertiesInNetworks
     */
    public function setProfileProperty(UserProfile $profileProperty)
    {
        $this->profileProperty = $profileProperty;
    
        return $this;
    }

    /**
     * Get profileProperty
     *
     * @return UserProfile
     */
    public function getProfileProperty()
    {
        return $this->profileProperty;
    }

    /**
     * Set network
     *
     * @param UsergMeshNetwork $network
     * @return ProfilePropertiesInNetworks
     */
    public function setNetwork(UsergMeshNetwork $network)
    {
        $this->network = $network;
    
        return $this;
    }

    /**
     * Get network
     *
     * @return UsergMeshNetwork
     */
    public function getNetwork()
    {
        return $this->network;
    }
}