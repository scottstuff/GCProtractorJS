<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PasswordLog Entity.
 *
 * Tracks user password changes, in addition to the tracking present in ActivityLog.
 * Idea is that the activity log will get very large and become unwieldy for querying
 * a user's password changes. Also separating it gives us more functionality, like if
 * we want to enforce higher security by expiring credentials and disallowing users
 * to set recently-used passwords.
 *
 * @ORM\Table(name="password_logs")
 * @ORM\Entity
 */
class PasswordLog
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
     * @var \DateTime
     *
     * @ORM\Column(name="dateChanged", type="datetime")
     */
    private $dateChanged;

    /**
     * @var string
     *
     * @ORM\Column(name="previousHash", type="string", length=255)
     */
    private $previousHash;

    /**
     * @var string
     * @ORM\Column(name="ipAddress", type="string", length=15)
     */
    private $ipAddress;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * Creates a PasswordLog object from the given user, storing the current time and the user's
     * current password hash.
     *
     * @param User $user
     * @param $ipAddr
     * @return PasswordLog
     */
    public static function make(User $user, $ipAddr)
    {
        $log = new PasswordLog();
        $log->setUser($user);
        $log->setIpAddress($ipAddr);
        $log->setPreviousHash($user->getPassword());

        return $log;
    }

    public function __construct()
    {
        $this->dateChanged = new \DateTime('now');
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
     * Set dateChanged
     *
     * @param \DateTime $dateChanged
     * @return PasswordLog
     */
    public function setDateChanged($dateChanged)
    {
        $this->dateChanged = $dateChanged;
    
        return $this;
    }

    /**
     * Get dateChanged
     *
     * @return \DateTime 
     */
    public function getDateChanged()
    {
        return $this->dateChanged;
    }

    /**
     * Set previousHash
     *
     * @param string $previousHash
     * @return PasswordLog
     */
    public function setPreviousHash($previousHash)
    {
        $this->previousHash = $previousHash;
    
        return $this;
    }

    /**
     * Get previousHash
     *
     * @return string 
     */
    public function getPreviousHash()
    {
        return $this->previousHash;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }
}
