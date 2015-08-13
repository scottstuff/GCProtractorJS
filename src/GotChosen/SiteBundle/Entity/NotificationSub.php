<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationSub
 *
 * @ORM\Table(name="NotificationSubscriptions")
 * @ORM\Entity
 */
class NotificationSub
{
    /**
     * @var NotificationType
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="NotificationType")
     * @ORM\JoinColumn(name="idNotificationType", referencedColumnName="idNotificationTypes")
     */
    private $notificationType;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notificationSubs")
     * @ORM\JoinColumn(name="idUser", referencedColumnName="id")
     */
    private $user;

    public static function make(User $user, NotificationType $type)
    {
        $sub = new NotificationSub();
        $sub->setUser($user);
        $sub->setNotificationType($type);

        return $sub;
    }

    public function equals(NotificationSub $other)
    {
        return $other->getNotificationType()->getId() == $this->getNotificationType()->getId()
            && $other->getUser()->getId() == $this->getUser()->getId();
    }

    public function __construct()
    {

    }

    /**
     * Set notificationType
     *
     * @param NotificationType $notificationType
     * @return NotificationSub
     */
    public function setNotificationType(NotificationType $notificationType = null)
    {
        $this->notificationType = $notificationType;
    
        return $this;
    }

    /**
     * Get notificationType
     *
     * @return NotificationType
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return NotificationSub
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
}
