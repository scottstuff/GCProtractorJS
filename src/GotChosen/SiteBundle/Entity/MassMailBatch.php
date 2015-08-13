<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MassMailBatch
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MassMailBatch
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
     * @ORM\Column(name="day", type="date")
     */
    private $day;

    /**
     * @var integer
     *
     * @ORM\Column(name="hour", type="smallint")
     */
    private $hour;

    /**
     * @var integer
     *
     * @ORM\Column(name="messagesSent", type="integer")
     */
    private $messagesSent;


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
     * Set day
     *
     * @param \DateTime $day
     * @return MassMailBatch
     */
    public function setDay($day)
    {
        $this->day = $day;
    
        return $this;
    }

    /**
     * Get day
     *
     * @return \DateTime 
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set hour
     *
     * @param integer $hour
     * @return MassMailBatch
     */
    public function setHour($hour)
    {
        $this->hour = $hour;
    
        return $this;
    }

    /**
     * Get hour
     *
     * @return integer 
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * Set messagesSent
     *
     * @param integer $messagesSent
     * @return MassMailBatch
     */
    public function setMessagesSent($messagesSent)
    {
        $this->messagesSent = $messagesSent;
    
        return $this;
    }

    /**
     * Get messagesSent
     *
     * @return integer 
     */
    public function getMessagesSent()
    {
        return $this->messagesSent;
    }
}
