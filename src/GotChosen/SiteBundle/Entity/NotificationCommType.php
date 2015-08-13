<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationCommType
 *
 * @ORM\Table(name="NotificationCommType")
 * @ORM\Entity
 */
class NotificationCommType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="idNotificationCommType", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="NotificationCommTypeName", type="string", length=100)
     */
    private $typeName;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="NotificationType", mappedBy="commType")
     */
    private $notificationTypes;

    public function __construct()
    {
        $this->notificationTypes = new ArrayCollection();
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
     * Set notificationCommTypeName
     *
     * @param string $notificationCommTypeName
     * @return NotificationCommType
     */
    public function setTypeName($notificationCommTypeName)
    {
        $this->typeName = $notificationCommTypeName;
    
        return $this;
    }

    /**
     * Get notificationCommTypeName
     *
     * @return string 
     */
    public function getTypeName()
    {
        return $this->typeName;
    }
}