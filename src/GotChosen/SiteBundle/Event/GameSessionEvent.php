<?php

namespace GotChosen\SiteBundle\Event;

use GotChosen\SiteBundle\Entity\EGPlaySession;
use Symfony\Component\EventDispatcher\Event;

class GameSessionEvent extends Event
{
    /**
     * @var EGPlaySession
     */
    private $playSession;

    public function __construct(EGPlaySession $session)
    {
        $this->playSession = $session;
    }

    public function getPlaySession()
    {
        return $this->playSession;
    }
}