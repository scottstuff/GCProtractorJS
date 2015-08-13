<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GotChosen\Util\Enums;

/**
 * UserProfile
 *
 * @ORM\Table(uniqueConstraints={
 *      @ORM\UniqueConstraint(name="UniqueIndexUToP", columns={"user_id", "property_id"})
 *                              },
 *      indexes={@ORM\Index(name="PropertyValue", columns={"propertyValue"})}
 * )
 * @ORM\Entity
 */
class UserProfile
{
    // inprogress: adding visibility levels back in. only having permissions work off of custom networks
    // would be very clumsy/hackish when dealing with visibilities like "everyone" and "all registered members".

    // global property security levels - private, public, all registered members.
    // numbers matched up to values in current GotChosen system.
    // can be overridden to only share with specific networks, permissions then stored in ProfilePropertiesInNetworks
    const VISIBLE_PUBLIC = 0;
    const VISIBLE_MEMBERS = 1;
    const VISIBLE_PRIVATE = 2;
    const VISIBLE_CUSTOM = 3;

    static public $visibilities = [
        self::VISIBLE_PUBLIC => 'Public',
        self::VISIBLE_MEMBERS => 'Members Only',
        self::VISIBLE_PRIVATE => 'Private',
        self::VISIBLE_CUSTOM => 'Specific Networks',
    ];

    /**
     * Necessary for ProfilePropertiesInNetworks to point to.
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ProfileProperty
     *
     * @ORM\ManyToOne(targetEntity="ProfileProperty")
     */
    private $property;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="profile")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="propertyValue", type="string", length=1000)
     */
    private $propertyValue;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isSearchableByDefault", type="boolean")
     */
    private $isSearchableByDefault = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastModified", type="datetime")
     */
    private $lastModified;

    /**
     * @var integer One of UserProfile::VISIBLE_* constants.
     *
     * @ORM\Column(name="visibility", type="smallint")
     */
    private $visibility;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ProfilePropertiesInNetworks", mappedBy="profileProperty")
     */
    private $visibleNetworks;

    private $visibleNetworkIdCache = null;

    public function __construct()
    {
        $this->visibleNetworks = new ArrayCollection();
        $this->lastModified = new \DateTime('now');
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Determines if this property can be seen by the given $viewer.
     * If $viewer is the property's owner, or an administrator, returns true. Otherwise it will compare
     * this property's visible networks with the networks of the viewer, and if at least one matches,
     * this method will return true.
     *
     * @param User $viewer
     * @param array $viewerNetworkIds
     * @return bool
     */
    public function isVisibleBy(User $viewer = null, $viewerNetworkIds = null)
    {
        $visibility = $this->getVisibility();

        // if public, true right away. if not public and the viewer is a guest, false right away.
        if ( $visibility == self::VISIBLE_PUBLIC ) {
            return true;
        } else if ( !$viewer ) {
            return false;
        }

        // if we are viewing our own profile, true.
        if ( $viewer && $viewer->getId() == $this->getUser()->getId() ) {
            return true;
        }

        // if we are an admin, true.
        // however we name these... ROLE_ADMIN is part of symfony, Administrators may be a new thing.
        $viewerRoles = $viewer ? $viewer->getRoles() : [];
        if ( in_array('ROLE_SUPER_ADMIN', $viewerRoles) || in_array('Administrator', $viewerRoles) ) {
            return true;
        }

        // handle private (false except for owner/admin, handled above), and all registered users.
        if ( $visibility == self::VISIBLE_PRIVATE ) {
            return false;
        } else if ( $visibility == self::VISIBLE_MEMBERS ) {
            return $viewer ? true : false;
        }

        // handle custom network-based visibility.

        // if a list of the viewer's network ids is passed, use that to save on processing time.
        if ( $viewerNetworkIds === null ) {
            $viewerNetworkIds = [];
            foreach ( $viewer->getNetworks() as $vnet ) {
                $viewerNetworkIds[] = $vnet->getNetwork()->getId();
            }
        }

        return $this->hasIntersectingNetworks($viewerNetworkIds);
    }

    public function hasIntersectingNetworks(array $otherNetworkIds)
    {
        if ( $this->visibleNetworkIdCache === null ) {
            $this->visibleNetworkIdCache = [];
            foreach ( $this->visibleNetworks as $network ) {
                $this->visibleNetworkIdCache[] = $network->getNetwork()->getId();
            }
        }

        return count(array_intersect($this->visibleNetworkIdCache, $otherNetworkIds)) > 0;
    }

    /*
    public function addDefaultVisibleNetwork()
    {
        $property = $this->getProperty();
        $network = $property->getDefaultNetworkVisibility();

        $mapping = new ProfilePropertiesInNetworks();
        $mapping->setProfileProperty($this);
        $mapping->setNetwork($network);

        $this->addVisibleNetwork($mapping);

        return $mapping;
    }
    */

    /**
     * Set propertyValue
     *
     * @param string $propertyValue
     * @return UserProfile
     */
    public function setPropertyValue($propertyValue)
    {
        $this->propertyValue = $propertyValue;

        return $this;
    }

    /**
     * Get propertyValue
     *
     * @return string
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }

    /**
     * Set isSearchableByDefault
     *
     * @param boolean $isSearchableByDefault
     * @return UserProfile
     */
    public function setIsSearchableByDefault($isSearchableByDefault)
    {
        $this->isSearchableByDefault = $isSearchableByDefault;

        return $this;
    }

    /**
     * Get isSearchableByDefault
     *
     * @return boolean
     */
    public function getIsSearchableByDefault()
    {
        return $this->isSearchableByDefault;
    }

    /**
     * Set lastModified
     *
     * @param \DateTime $lastModified
     * @return UserProfile
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * Get lastModified
     *
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set property
     *
     * @param ProfileProperty $property
     * @return UserProfile
     */
    public function setProperty(ProfileProperty $property = null)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get property
     *
     * @return ProfileProperty
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return UserProfile
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

    /**
     * @param $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        Enums::assert($visibility, self::$visibilities);
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Add visibleNetworks
     *
     * @param ProfilePropertiesInNetworks $visibleNetworks
     * @return UserProfile
     */
    public function addVisibleNetwork(ProfilePropertiesInNetworks $visibleNetworks)
    {
        $this->visibleNetworks[] = $visibleNetworks;

        return $this;
    }

    /**
     * Remove visibleNetworks
     *
     * @param ProfilePropertiesInNetworks $visibleNetworks
     */
    public function removeVisibleNetwork(ProfilePropertiesInNetworks $visibleNetworks)
    {
        $this->visibleNetworks->removeElement($visibleNetworks);
    }

    /**
     * Get visibleNetworks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVisibleNetworks()
    {
        return $this->visibleNetworks;
    }
}
