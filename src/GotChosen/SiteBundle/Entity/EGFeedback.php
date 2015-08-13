<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EGFeedback
 *
 * @ORM\Table(name="EGFeedback")
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\EGFeedbackRepository")
 */
class EGFeedback
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
     * @var EGGame
     * @ORM\ManyToOne(targetEntity="EGGame")
     */
    private $game;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="feedbackContent", type="string", length=500)
     */
    private $feedbackContent;

    /**
     * @var integer
     *
     * @ORM\Column(name="developerRating", type="smallint")
     */
    private $developerRating;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ratedDate", type="datetime", nullable=true)
     */
    private $ratedDate;


    public function __construct()
    {
        $this->createdDate = new \DateTime('now');
        $this->developerRating = -1;
        $this->ratedDate = null;
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
     * Set feedbackContent
     *
     * @param string $feedbackContent
     * @return EGFeedback
     */
    public function setFeedbackContent($feedbackContent)
    {
        $this->feedbackContent = $feedbackContent;
    
        return $this;
    }

    /**
     * Get feedbackContent
     *
     * @return string 
     */
    public function getFeedbackContent()
    {
        return $this->feedbackContent;
    }

    /**
     * Set developerRating
     *
     * @param integer $developerRating
     * @return EGFeedback
     */
    public function setDeveloperRating($developerRating)
    {
        $this->developerRating = $developerRating;
    
        return $this;
    }

    /**
     * Get developerRating
     *
     * @return integer 
     */
    public function getDeveloperRating()
    {
        return $this->developerRating;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return EGFeedback
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
     * Set ratedDate
     *
     * @param \DateTime $ratedDate
     * @return EGFeedback
     */
    public function setRatedDate($ratedDate)
    {
        $this->ratedDate = $ratedDate;
    
        return $this;
    }

    /**
     * Get ratedDate
     *
     * @return \DateTime 
     */
    public function getRatedDate()
    {
        return $this->ratedDate;
    }

    /**
     * Set game
     *
     * @param EGGame $game
     * @return EGFeedback
     */
    public function setGame(EGGame $game = null)
    {
        $this->game = $game;
    
        return $this;
    }

    /**
     * Get game
     *
     * @return EGGame
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return EGFeedback
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