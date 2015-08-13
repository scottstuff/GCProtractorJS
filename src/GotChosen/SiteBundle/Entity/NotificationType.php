<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationType
 *
 * @ORM\Table(name="NotificationTypes")
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\NotificationTypeRepository")
 */
class NotificationType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="idNotificationTypes", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="NotificationTypesName", type="string", length=100)
     */
    private $name;

    /**
     * @var NotificationCommType
     *
     * @ORM\ManyToOne(targetEntity="NotificationCommType", inversedBy="notificationTypes")
     * @ORM\JoinColumn(name="NotificationCommType", referencedColumnName="idNotificationCommType")
     */
    private $commType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="IsDefault", type="boolean")
     */
    private $isDefault;

    public function __construct()
    {
        $this->isDefault = false;
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
     * Set name
     *
     * @param string $name
     * @return NotificationType
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set commType
     *
     * @param NotificationCommType $commType
     * @return NotificationType
     */
    public function setCommType(NotificationCommType $commType = null)
    {
        $this->commType = $commType;
    
        return $this;
    }

    /**
     * Get commType
     *
     * @return NotificationCommType
     */
    public function getCommType()
    {
        return $this->commType;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return NotificationType
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    
        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean 
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }
}
