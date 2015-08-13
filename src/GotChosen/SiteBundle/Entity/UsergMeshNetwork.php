<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * UsergMeshNetwork
 *
 * @ORM\Table(name="UsergMeshNetworks",
 *            indexes={ @ORM\Index(name="NetworkName", columns={"networkName"}) }
 * )
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\UsergMeshNetworkRepository")
 */
class UsergMeshNetwork
{
    /**
     * @var integer
     *
     * @ORM\Column(name="networkId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="networkName", type="string", length=100)
     */
    private $networkName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDeleted", type="boolean")
     */
    private $isDeleted = false;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ownedNetworks")
     * @ORM\JoinColumn(name="ownerUserId")
     */
    private $ownerUser;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UsersInNetworks", mappedBy="network")
     */
    private $users;

    public function __construct()
    {
        $this->createdDate = new \DateTime('now');
        $this->users = new ArrayCollection();
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
     * Set networkName
     *
     * @param string $networkName
     * @return UsergMeshNetwork
     */
    public function setNetworkName($networkName)
    {
        $this->networkName = $networkName;
    
        return $this;
    }

    /**
     * Get networkName
     *
     * @return string 
     */
    public function getNetworkName()
    {
        return $this->networkName;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return UsergMeshNetwork
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
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return UsergMeshNetwork
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;
    
        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean 
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set ownerUser
     *
     * @param User $ownerUser
     * @return UsergMeshNetwork
     */
    public function setOwnerUser(User $ownerUser = null)
    {
        $this->ownerUser = $ownerUser;
    
        return $this;
    }

    /**
     * Get ownerUser
     *
     * @return User
     */
    public function getOwnerUser()
    {
        return $this->ownerUser;
    }

    /**
     * Add users
     *
     * @param UsersInNetworks $users
     * @return UsergMeshNetwork
     */
    public function addUser(UsersInNetworks $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param UsersInNetworks $users
     */
    public function removeUser(UsersInNetworks $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}