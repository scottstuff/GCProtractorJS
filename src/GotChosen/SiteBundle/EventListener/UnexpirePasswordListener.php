<?php

namespace GotChosen\SiteBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use GotChosen\SiteBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for when users reset their password and updates the credentials_expired field to 0.
 *
 * @package GotChosen\SiteBundle\EventListener
 */
class UnexpirePasswordListener implements EventSubscriberInterface
{
    private $manager;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::RESETTING_RESET_COMPLETED => 'resetCompleted',
        ];
    }

    public function resetCompleted(FilterUserResponseEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();
        $user->setCredentialsExpired(false);

        $this->manager->updateUser($user);
    }
}