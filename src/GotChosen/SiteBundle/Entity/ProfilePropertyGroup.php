<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GotChosen\Util\Enums;

/**
 * ProfilePropertyGroup
 *
 * @ORM\Table(name="ProfilePropertyGroups")
 * @ORM\Entity
 */
class ProfilePropertyGroup
{
    /**
     * @var integer
     *
     * @ORM\Column(name="idProfilePropertyGroups", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="GroupName", type="string", length=200)
     */
    private $groupName;

    /**
     * @var integer
     *
     * @ORM\Column(name="visibility", type="smallint")
     */
    private $visibility;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ProfileProperty", inversedBy="groups")
     * @ORM\JoinTable(name="ProfilePropertyGroupMap",
     *      joinColumns={@ORM\JoinColumn(name="idGroup", referencedColumnName="idProfilePropertyGroups")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="idProfileProperty", referencedColumnName="id")}
     * )
     */
    private $properties;

    public static function make($name, $visibility)
    {
        $group = new ProfilePropertyGroup();
        $group->setGroupName($name);
        $group->setVisibility($visibility);
        return $group;
    }

    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    public function getSlug()
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]/', '-', $this->getGroupName()));
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
     * Set groupName
     *
     * @param string $groupName
     * @return ProfilePropertyGroup
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
    
        return $this;
    }

    /**
     * Get groupName
     *
     * @return string 
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * Set visibility
     *
     * @param integer $visibility
     * @return ProfilePropertyGroup
     */
    public function setVisibility($visibility)
    {
        Enums::assert($visibility, UserProfile::$visibilities);

        $this->visibility = $visibility;
    
        return $this;
    }

    /**
     * Get visibility
     *
     * @return integer 
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Add properties
     *
     * @param ProfileProperty $properties
     * @return ProfilePropertyGroup
     */
    public function addProperty(ProfileProperty $property)
    {
        $property->addGroup($this);
        $this->properties[] = $property;
    
        return $this;
    }

    /**
     * Remove properties
     *
     * @param ProfileProperty $properties
     */
    public function removeProperty(ProfileProperty $property)
    {
        $property->removeGroup($this);
        $this->properties->removeElement($property);
        
        return $this;
    }

    /**
     * Get properties
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProperties()
    {
        return $this->properties;
    }
}