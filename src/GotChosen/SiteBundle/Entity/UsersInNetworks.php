<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersInNetworks
 *
 * @ORM\Table(name="Users_In_Networks", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="UniqueIndexNToU", columns={"networkId", "userId"})
 * })
 * @ORM\Entity
 */
class UsersInNetworks
{
    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="networks")
     * @ORM\JoinColumn(name="userId")
     */
    private $user;

    /**
     * @var UsergMeshNetwork
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UsergMeshNetwork", inversedBy="users")
     * @ORM\JoinColumn(name="networkId", referencedColumnName="networkId")
     */
    private $network;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return UsersInNetworks
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
     * Set user
     *
     * @param User $user
     * @return UsersInNetworks
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
     * Set network
     *
     * @param UsergMeshNetwork $network
     * @return UsersInNetworks
     */
    public function setNetwork(UsergMeshNetwork $network = null)
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