<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use GotChosen\Util\Strings;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * User
 *
 * @ORM\Table(indexes={
 *      @ORM\Index(name="Username", columns={"username"}),
 *      @ORM\Index(name="Username_Password", columns={"username", "password", "salt"}),
 *      @ORM\Index(name="Status", columns={"status"}),
 *      @ORM\Index(name="CreatedDate", columns={"createdDate"}),
 *      @ORM\Index(name="LastModified", columns={"lastModified"}),
 *      @ORM\Index(name="ConfirmationToken", columns={"confirmation_token"})}
 * )
 * @ORM\Entity(repositoryClass="GotChosen\SiteBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * User status constants.
     *
     * ACTIVE = Verified account.
     * AWAITING_VERIFICATION = Confirmation e-mail pending.
     * NOT_CONVERTED = Old platform user, still GUID name.
     * DISABLED_USER = User "deleted" themselves through profile.
     * DISABLED_ADMIN = Admin manually disabled user.
     * BAD_EMAIL = User auto-disabled for having a bad e-mail address.
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_AWAITING_VERIFICATION = 'unconfirmed';
    const STATUS_NOT_CONVERTED = 'not_converted';
    const STATUS_DISABLED_USER = 'user_disabled';
    const STATUS_DISABLED_ADMIN = 'admin_disabled';
    const STATUS_BAD_EMAIL = 'bad_email';

    public static $status_types = [
        self::STATUS_ACTIVE => 'Active User',
        self::STATUS_AWAITING_VERIFICATION => 'Unconfirmed User',
        self::STATUS_NOT_CONVERTED => 'Unconverted Old Platform User',
        self::STATUS_DISABLED_USER => 'Disabled User (Self)',
        self::STATUS_DISABLED_ADMIN => 'Disabled User (Admin)',
        self::STATUS_BAD_EMAIL => 'Disabled User (Bad E-mail)'
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdDate", type="datetime")
     */
    protected $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastModified", type="datetime")
     */
    protected $lastModified;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     */
    protected $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="tokens", type="integer")
     */
    protected $tokens;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UserProfile", mappedBy="user")
     */
    protected $profile;

    /**
     * Named "customRoles" since "roles" already exists in the base model.
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     */
    protected $customRoles;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UsergMeshNetwork", mappedBy="ownerUser")
     */
    protected $ownedNetworks;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="UsersInNetworks", mappedBy="user")
     */
    protected $networks;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ScholarshipEntry", mappedBy="user")
     */
    protected $scholarshipEntries;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="NotificationSub", mappedBy="user")
     */
    protected $notificationSubs;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalWins", type="integer")
     */
    protected $totalWins;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalLosses", type="integer")
     */
    protected $totalLosses;

    /**
     * @var array
     */
    protected $cachedPropertyValues = null;

    /**
     * Constructor
     */

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Video", mappedBy="user")
     */
    private $videos;

    public function __construct()
    {
        parent::__construct();

        $this->profile = new ArrayCollection();
        $this->customRoles = new ArrayCollection();
        $this->ownedNetworks = new ArrayCollection();
        $this->networks = new ArrayCollection();
        $this->scholarshipEntries = new ArrayCollection();
        $this->notificationSubs = new ArrayCollection();
        $this->videos = new ArrayCollection();

        $this->createdDate = new \DateTime('now');
        $this->lastModified = new \DateTime('now');

        $this->tokens = 0;
        $this->totalWins = 0;
        $this->totalLosses = 0;
    }

    /**
     * Overrides BaseUser::getRoles so we can merge additional custom roles from the DB.
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = parent::getRoles();
        /** @var $customRole Role */
        foreach ( $this->getCustomRoles() as $customRole ) {
            $roles[] = $customRole->getRoleName();
        }

        return array_unique($roles);
    }

    protected function cacheProperties()
    {
        if ( is_array($this->cachedPropertyValues) ) {
            return;
        }

        $this->cachedPropertyValues = [];

        /** @var $profileProp UserProfile */
        foreach ( $this->profile as $profileProp ) {
            $key = $profileProp->getProperty()->getName();
            $value = $profileProp->getPropertyValue();
            $this->cachedPropertyValues[$key] = $value;
        }
    }

    public function getCachedProperties()
    {
        $this->cacheProperties();
        return $this->cachedPropertyValues;
    }

    /**
     * @param array $properties
     * @internal
     * @see UserRepository::precacheProperties
     */
    public function setCachedProperties(array $properties)
    {
        $this->cachedPropertyValues = $properties;
    }

    public function getPropertyValue($key, $default = null)
    {
        $this->cacheProperties();

        return isset($this->cachedPropertyValues[$key]) ? $this->cachedPropertyValues[$key] : $default;
    }

    public function getFullName()
    {
        return $this->getPropertyValue("FirstName", '') . ' ' . $this->getPropertyValue("LastName", '');
    }

    public function getScholarshipEntry(Scholarship $sship)
    {
        /** @var ScholarshipEntry[] $entries */
        $entries = $this->getScholarshipEntries();
        foreach ( $entries as $entry ) {
            if ( $entry->getScholarship()->getId() == $sship->getId() ) {
                return $entry;
            }
        }
        return null;
    }

    public function hasApplied(Scholarship $sship)
    {
        return $this->getScholarshipEntry($sship) !== null;
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
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return User
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
     * Set lastModified
     *
     * @param \DateTime $lastModified
     * @return User
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
     * Set status
     *
     * @param string $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status/
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param $tokens
     * @return $this
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
        return $this;
    }

    /**
     * Add profile
     *
     * @param UserProfile $profile
     * @return User
     */
    public function addProfile(UserProfile $profile)
    {
        $this->profile[] = $profile;

        return $this;
    }

    /**
     * Remove profile
     *
     * @param UserProfile $profile
     */
    public function removeProfile(UserProfile $profile)
    {
        $this->profile->removeElement($profile);
    }

    /**
     * Get profile
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Add ownedNetworks
     *
     * @param UsergMeshNetwork $ownedNetworks
     * @return User
     */
    public function addOwnedNetwork(UsergMeshNetwork $ownedNetworks)
    {
        $this->ownedNetworks[] = $ownedNetworks;

        return $this;
    }

    /**
     * Remove ownedNetworks
     *
     * @param UsergMeshNetwork $ownedNetworks
     */
    public function removeOwnedNetwork(UsergMeshNetwork $ownedNetworks)
    {
        $this->ownedNetworks->removeElement($ownedNetworks);
    }

    /**
     * Get ownedNetworks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnedNetworks()
    {
        return $this->ownedNetworks;
    }

    /**
     * Add networks
     *
     * @param UsersInNetworks $networks
     * @return User
     */
    public function addNetwork(UsersInNetworks $networks)
    {
        $this->networks[] = $networks;

        return $this;
    }

    /**
     * Remove networks
     *
     * @param UsersInNetworks $networks
     */
    public function removeNetwork(UsersInNetworks $networks)
    {
        $this->networks->removeElement($networks);
    }

    /**
     * Get networks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNetworks()
    {
        return $this->networks;
    }

    /**
     * Add customRoles
     *
     * @param Role $customRoles
     * @return User
     */
    public function addCustomRole(Role $customRoles)
    {
        $this->customRoles[] = $customRoles;

        return $this;
    }

    /**
     * Remove customRoles
     *
     * @param Role $customRoles
     */
    public function removeCustomRole(Role $customRoles)
    {
        $this->customRoles->removeElement($customRoles);
    }

    /**
     * Get customRoles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomRoles()
    {
        return $this->customRoles;
    }

    /**
     * Add scholarshipEntries
     *
     * @param ScholarshipEntry $scholarshipEntries
     * @return User
     */
    public function addScholarshipEntry(ScholarshipEntry $scholarshipEntries)
    {
        $this->scholarshipEntries[] = $scholarshipEntries;

        return $this;
    }

    /**
     * Remove scholarshipEntries
     *
     * @param ScholarshipEntry $scholarshipEntries
     */
    public function removeScholarshipEntry(ScholarshipEntry $scholarshipEntries)
    {
        $this->scholarshipEntries->removeElement($scholarshipEntries);
    }

    /**
     * Get scholarshipEntries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getScholarshipEntries()
    {
        return $this->scholarshipEntries;
    }

    public function addNotificationSub(NotificationSub $n)
    {
        $this->notificationSubs[] = $n;
        return $this;
    }

    public function removeNotificationSub(NotificationSub $n)
    {
        $this->notificationSubs->removeElement($n);
    }

    public function getNotificationSubs()
    {
        return $this->notificationSubs;
    }

    /**
     * This seems hacky as fuck.
     */
    public function hasNotificationSubByTypeName($name)
    {
        foreach ( $this->getNotificationSubs() as $sub ) {
            if ( $sub->getNotificationType()->getName() == $name ) {
                return true;
            }
        }
        return false;
    }

    public function hasNotificationSub(NotificationSub $n)
    {
        /** @var NotificationSub $sub */
        foreach ( $this->getNotificationSubs() as $sub ) {
            if ( $n->equals($sub) ) {
                return $sub;
            }
        }
        return false;
    }

    public function generateUnsubscribeLink(NotificationType $type, RouterInterface $urlGen)
    {
        $email = Strings::base64EncodeUrl($this->getEmail());
        return $urlGen->generate('user_unsubscribe', ['type' => $type->getId(), 'email' => $email],
            UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @return int
     */
    public function getTotalWins()
    {
        return $this->totalWins;
    }

    /**
     * @param int $totalWins
     * @return $this
     */
    public function setTotalWins($totalWins)
    {
        $this->totalWins = $totalWins;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalLosses()
    {
        return $this->totalLosses;
    }

    /**
     * @param int $totalLosses
     * @return $this
     */
    public function setTotalLosses($totalLosses)
    {
        $this->totalLosses = $totalLosses;
        return $this;
    }
}
