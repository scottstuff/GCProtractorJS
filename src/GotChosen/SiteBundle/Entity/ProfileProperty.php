<?php

namespace GotChosen\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use GotChosen\SiteBundle\Entity\ProfilePropertyGroup;
use GotChosen\SiteBundle\Form\Type\StateType;
use GotChosen\Util\Enums;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * ProfileProperty
 *
 * @ORM\Table(indexes={ @ORM\Index(name="PropertyName", columns={"name"}) })
 * @ORM\Entity
 */
class ProfileProperty
{
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_DATE = 'date';
    const TYPE_CHOICE = 'choice';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_FILE = 'file';

    public static $fieldTypes = [
        self::TYPE_TEXT => 'Single-Line Text Box',
        self::TYPE_TEXTAREA => 'Text Area',
        self::TYPE_DATE => 'Date Selector',
        self::TYPE_CHOICE => 'Option List',
        self::TYPE_CHECKBOX => 'Single Checkbox',
        self::TYPE_FILE => 'File',
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isRequired", type="boolean")
     */
    private $isRequired;

    /**
     * @var integer One of UserProfile::VISIBLE_* constants.
     *
     * @ORM\Column(name="defaultVisibility", type="smallint")
     */
    private $defaultVisibility;

    /**
     * @var string One of ProfileProperty::TYPE_* constants.
     *
     * @ORM\Column(name="fieldType", type="string", length=20)
     */
    private $fieldType;

    /**
     * Options for the field, like list items, etc.
     * @var array
     *
     * @ORM\Column(name="fieldOptions", type="json_array")
     */
    private $fieldOptions;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ProfilePropertyGroup", mappedBy="properties")
     */
    private $groups;

    public function __construct() {
        $this->groups = new ArrayCollection();
    }

    /**
     * Creates the form element and adds it to the given builder based on field configuration.
     *
     * @param FormBuilderInterface $builder
     * @param bool $mapped
     * @param array $extraOptions
     */
    public function createFormElement(FormBuilderInterface $builder, $mapped = false, $extraOptions = [])
    {
        static $defaults = [
            'email' => false,
            'required' => true,
            'date_years' => '',
            'date_years_rel' => '',

            'country' => false,
            'state' => false,
            'choices' => [],

            'rows' => 4,
            'class' => '',
            'expanded' => false,
            'title' => '',
        ];

        $defaults['required'] = $this->getIsRequired();
        $defaults['date_years'] = (date('Y') - 10) . '-' . (date('Y') - 100);
        $options = array_replace($defaults, $this->getFieldOptions());
        $options = array_replace($options, $extraOptions);

        $formOptions = [
            'label' => 'profile_properties.' . $this->name,
            'translation_domain' => 'profile_properties',
            'required' => $options['required'],
            'mapped' => $mapped,
            'render_optional_text' => false,
            'attr' => [],
            'constraints' => [],
            'error_type' => 'inline',
        ];

        if ( !empty($options['title']) ) {
            $formOptions['attr']['title'] = $options['title'];
        }

        if ( !empty($options['required']) ) {
            $formOptions['constraints'][] = new NotBlank();
        }

        if ( !empty($options['constraints']) ) {
            $formOptions['constraints'] = array_merge($formOptions['constraints'], $options['constraints']);
        }

        switch ( $this->fieldType ) {
            case self::TYPE_TEXT: {
                if ( $options['email'] ) {
                    $formOptions['constraints'][] = new Email();
                }
                $builder->add($this->name, $options['email'] ? 'email' : 'text', $formOptions);
                break;
            }
            case self::TYPE_TEXTAREA: {
                $formOptions['attr'] = array_replace($formOptions['attr'], [
                    'rows' => $options['rows'],
                    'class' => $options['class'],
                ]);
                $formOptions['max_length'] = $options['max_length'];

                $builder->add($this->name, 'textarea', $formOptions);
                break;
            }
            case self::TYPE_DATE: {
                if ( !empty($options['date_years_rel']) ) {
                    $rng = explode(':', $options['date_years_rel']);
                    $now = (int) date('Y');
                    $yearRange = [$now + intval($rng[0]), $now + intval($rng[1])];
                } else {
                    $yearRange = explode('-', $options['date_years']);
                }
                $formOptions['years'] = range((int) $yearRange[0], (int) $yearRange[1]);
                $formOptions['empty_value'] = '';
                $builder->add($this->name, 'date', $formOptions);
                break;
            }
            case self::TYPE_CHOICE: {
                if ( $options['country'] ) {
                    $formOptions['preferred_choices'] = ['US'];
                } else if ( !$options['state'] ) {
                    $formOptions['choices'] = $options['choices'];
                }

                $formOptions['expanded'] = $options['expanded'];
                if ( $this->getName() == 'Gender' ) {
                    $formOptions['attr']['class'] = 'single-line';
                }

                if ( $options['country'] ) {
                    $type = 'country';
                } else if ( $options['state'] ) {
                    $type = new StateType();
                } else {
                    $type = 'choice';
                }
                $builder->add($this->name, $type, $formOptions);
                break;
            }
            case self::TYPE_CHECKBOX: {
                $builder->add($this->name, 'checkbox', $formOptions);
                break;
            }
            case self::TYPE_FILE: {
                $builder->add($this->name, 'file', $formOptions);
                break;
            }
        }
    }

    public function shouldHidePrivacyControls()
    {
        // possibly have this DB-driven in the future, but this should do for now.
        $name = $this->getName();
        return in_array($name, ['FirstName', 'LastName']) or $this->hasGroupNamed("Additional Settings");
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
     * @return ProfileProperty
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
     * Set isRequired
     *
     * @param boolean $isRequired
     * @return ProfileProperty
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * Get isRequired
     *
     * @return boolean
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set defaultNetworkVisibility
     *
     * @param $defaultVisibility
     * @return ProfileProperty
     */
    public function setDefaultVisibility($defaultVisibility)
    {
        Enums::assert($defaultVisibility, UserProfile::$visibilities);
        $this->defaultVisibility = $defaultVisibility;
        return $this;
    }

    /**
     * Get defaultNetworkVisibility
     *
     * @return UsergMeshNetwork
     */
    public function getDefaultVisibility()
    {
        return $this->defaultVisibility;
    }

    /**
     * Set fieldType
     *
     * @param string $fieldType
     * @return ProfileProperty
     */
    public function setFieldType($fieldType)
    {
        Enums::assert($fieldType, self::$fieldTypes);
        $this->fieldType = $fieldType;

        return $this;
    }

    /**
     * Get fieldType
     *
     * @return string
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * Set fieldOptions
     *
     * @param array $fieldOptions
     * @return ProfileProperty
     */
    public function setFieldOptions($fieldOptions)
    {
        $this->fieldOptions = $fieldOptions;

        return $this;
    }

    /**
     * Get fieldOptions
     *
     * @return array
     */
    public function getFieldOptions()
    {
        return $this->fieldOptions ?: [];
    }

    /**
     * Add group
     *
     * @param ProfilePropertyGroup $group
     * @return ProfileProperty
     */
    public function addGroup(ProfilePropertyGroup $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param ProfilePropertyGroup $group
     * @return ProfileProperty
     */
    public function removeGroup(ProfilePropertyGroup $group)
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * Get groups
     *
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Has group
     *
     * @param ProfilePropertyGroup $group
     * @return boolean
     */
    public function hasGroup(ProfilePropertyGroup $group)
    {
        return $this->groups->contains($group);
    }

    /**
     * Has group by name
     *
     * @param string $groupName
     * @return boolean
     */
    public function hasGroupNamed($groupName)
    {
        foreach ( $this->groups as $group ) {
            if ( $group->getGroupName() == $groupName ) {
                return true;
            }
        }

        return false;
    }
}
