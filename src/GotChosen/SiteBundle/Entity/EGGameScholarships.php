<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GotChosen\Util\Enums;

/**
 * EGGameScholarships
 * 
 * @ORM\Table(name="game_scholarships", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="UniqueIndexGoS", columns={"game_id", "scholarship_id"})
 * })
 * @ORM\Entity
 */
class EGGameScholarships
{
    /**
     * Game scholarship type constants.
     */
    const TYPE_CONTEST = 'contest';
    const TYPE_CHAMPIONSHIP = 'championship';
    
    public static $scholarship_types = [
        self::TYPE_CONTEST => 'Contest',
        self::TYPE_CHAMPIONSHIP => 'Championship'
    ];
    
    /**
     * @var EGGame
     * 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EGGame", inversedBy="scholarships")
     */
    protected $game;
    
    /**
     * @var Scholarship
     * 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Scholarship")
     * @ORM\JoinColumn(referencedColumnName="idScholarships")
     */
    protected $scholarship;
    
    /**
     * @var string
     * 
     * @ORM\Column(type="string", length=50)
     */
    protected $scholarshipType;

    public static function make(EGGame $game, Scholarship $scholarship, $type)
    {
        $gs = new EGGameScholarships();
        $gs->setGame($game)
            ->setScholarship($scholarship)
            ->setScholarshipType($type);

        return $gs;
    }

    /**
     * @param EGGame $game
     * @return EGGameScholarships
     */
    public function setGame(EGGame $game)
    {
        $this->game = $game;
        
        return $this;
    }
    
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param Scholarship $scholarship
     * @return EGGameScholarships
     */
    public function setScholarship(Scholarship $scholarship)
    {
        $this->scholarship = $scholarship;
        
        return $this;
    }
    
    public function getScholarship()
    {
        return $this->scholarship;
    }
    
    public function setScholarshipType($scholarshipType)
    {
        Enums::assert($scholarshipType, self::$scholarship_types);
        
        $this->scholarshipType = $scholarshipType;
        
        return $this;
    }
    
    public function getScholarshipType()
    {
        return $this->scholarshipType;
    }
}
