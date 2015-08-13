<?php

namespace GotChosen\Mail;

/*
 * Mass mail filter spec.
 *
 * Description:
 * Need to filter recipients by language and products they're engaged with
 * Support user notification settings
 * "products they're engaged with" would be what scholarships they've signed up for.
 * Or game devs (that have submitted games).
 */
use Doctrine\ORM\EntityManager;
use GotChosen\SiteBundle\Entity\NotificationType;
use GotChosen\SiteBundle\Entity\Scholarship;

class Filter
{
    /**
     * @var string
     */
    private $language = null;

    /**
     * Associate the e-mail with a notification type, so it only sends out to users
     * that are accepting this type.
     *
     * @var NotificationType
     */
    private $notificationType = null;
    
    /**
     * The filter will match users based on their status in the DB.
     * 
     * @var string
     */
    private $userStatus = null;

    /**
     * The filter will match any user who has signed up for at least one of the listed
     * scholarships. If empty, matches all.
     *
     * @var Scholarship[]
     */
    private $scholarships = [];

    /**
     * If true, the filter will match users that have submitted a game to Evolution Games.
     *
     * @var bool
     */
    private $hasSubmittedGame = false;

    /**
     * @param Filter $filter
     * @return array
     */
    public static function toArray(Filter $filter)
    {
        $data = [];
        if ( $filter->language !== null ) {
            $data['language'] = $filter->language;
        }
        if ( $filter->notificationType !== null ) {
            $data['notificationTypeId'] = $filter->notificationType->getId();
        }
        if ( $filter->userStatus !== null ) {
            $data['userStatus'] = $filter->userStatus;
        }
        if ( !empty($filter->scholarships) ) {
            $data['scholarshipIds'] = [];
            foreach ( $filter->scholarships as $scholarship ) {
                $data['scholarshipIds'][] = $scholarship->getId();
            }
        }
        if ( $filter->hasSubmittedGame ) {
            $data['submittedGame'] = true;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param EntityManager $em
     * @return Filter
     */
    public static function fromArray(array $data, EntityManager $em)
    {
        $filter = new Filter();
        if ( isset($data['language']) ) {
            $filter->language = $data['language'];
        }
        if ( isset($data['notificationTypeId']) ) {
            $nt = $em->find('GotChosenSiteBundle:NotificationType', $data['notificationTypeId']);
            if ( $nt ) {
                $filter->notificationType = $nt;
            }
        }
        if ( isset($data['userStatus']) ) {
            $filter->userStatus = $data['userStatus'];
        }
        if ( isset($data['scholarshipIds']) ) {
            foreach ( $data['scholarshipIds'] as $ssid ) {
                $ss = $em->find('GotChosenSiteBundle:Scholarship', $ssid);
                if ( $ss ) {
                    $filter->scholarships[] = $ss;
                }
            }
        }
        if ( isset($data['submittedGame']) && $data['submittedGame'] ) {
            $filter->hasSubmittedGame = true;
        }

        return $filter;
    }

    public function isEmpty()
    {
        return $this->language === null && $this->notificationType === null && $this->userStatus === null
            && empty($this->scholarships) && !$this->hasSubmittedGame;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return NotificationType
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

    /**
     * @param NotificationType $notificationType
     * @return $this
     */
    public function setNotificationType(NotificationType $notificationType = null)
    {
        $this->notificationType = $notificationType;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getUserStatus()
    {
        return $this->userStatus;
    }
    
    /**
     * @param string $status
     * @return Filter
     */
    public function setUserStatus($status)
    {
        $this->userStatus = $status;
        return $this;
    }

    /**
     * @return Scholarship[]
     */
    public function getScholarships()
    {
        return $this->scholarships;
    }

    /**
     * @param Scholarship[] $scholarships
     * @return $this
     */
    public function setScholarships($scholarships)
    {
        $this->scholarships = $scholarships;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasSubmittedGame()
    {
        return $this->hasSubmittedGame;
    }

    /**
     * @param bool $submittedGame
     * @return $this
     */
    public function setHasSubmittedGame($submittedGame)
    {
        $this->hasSubmittedGame = $submittedGame;
        return $this;
    }
}