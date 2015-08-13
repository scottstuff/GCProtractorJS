<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * EGGameGenre
 *
 * @ORM\Table(name="GameGenres")
 * @ORM\Entity
 */
class EGGameGenre
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    protected $name;
    
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="EGGame", mappedBy="genre")
     */
    protected $games;
    
    public function __construct() {
        $this->games = new ArrayCollection();
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
     * @return EGGameGenre
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
     * Get games
     * 
     * @return ArrayCollection
     */
    public function getGames()
    {
        return $this->games;
    }
    
    /**
     * Add game
     *
     * @param EGGame $game
     * @return EGGameGenre
     */
    public function addGame(EGGame $game)
    {
        if ( !$this->games->contains($game) ) {
            $this->games->add($game);
        }
    
        return $this;
    }
    
    /**
     * Remove game
     * 
     * @param EGGame $game
     * @return EGGame
     */
    public function removeGame(EGGame $game)
    {
        if ( $this->games->contains($game) ) {
            $this->games->removeElement($game);
        }
        
        return $this;
    }
    
    /**
     * Has game
     * 
     * @param EGGame $game
     * @return boolean
     */
    public function hasGame(EGGame $game)
    {
        return $this->games->contains($game);
    }
}
