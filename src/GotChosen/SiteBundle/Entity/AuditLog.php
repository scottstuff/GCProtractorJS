<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuditLog
 *
 * @ORM\Table(name="ActivityLog")
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\AuditLogRepository")
 */
class AuditLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="idActivityLog", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ActivityDateTime", type="datetime")
     */
    private $createdDate;

    /**
     * @var string
     *
     * @ORM\Column(name="ActivityType", type="string", length=200)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="ClientIP4Address", type="string", length=15)
     */
    private $ipAddress;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="UserID", referencedColumnName="id")
     */
    private $user;

    public static function make(User $user, $action, $ip = '0.0.0.0')
    {
        $audit = new AuditLog();
        $audit->setUser($user);
        $audit->setAction($action);
        $audit->setIpAddress($ip);

        return $audit;
    }

    public function __construct()
    {
        $this->createdDate = new \DateTime('now');
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
     * @return AuditLog
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
     * Set action
     *
     * @param string $action
     * @return AuditLog
     */
    public function setAction($action)
    {
        $this->action = $action;
    
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return AuditLog
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

    public function setIpAddress($ip)
    {
        $this->ipAddress = $ip;
        return $this;
    }

    public function getIpAddress()
    {
        return $this->ipAddress;
    }
}