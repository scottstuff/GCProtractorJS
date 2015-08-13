<?php

namespace GotChosen\SiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Entity\ProfileProperty;
use GotChosen\SiteBundle\Entity\ProfilePropertyGroup;
use GotChosen\SiteBundle\Entity\UsergMeshNetwork;
use GotChosen\SiteBundle\Entity\UserProfile;

class LoadProfileProperties implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        $groupRepo = $manager->getRepository('GotChosenSiteBundle:ProfilePropertyGroup');

        $groupBasic = $groupRepo->findOneBy(['groupName' => 'Basic Information']);
        $groupContact = $groupRepo->findOneBy(['groupName' => 'Contact Information']);
        $groupEducation = $groupRepo->findOneBy(['groupName' => 'Education Information']);
        $groupSettings = $groupRepo->findOneBy(['groupName' => 'Additional Settings']);

        if ( !$groupBasic ) {
            $groupBasic = ProfilePropertyGroup::make('Basic Information', UserProfile::VISIBLE_PUBLIC);
            $manager->persist($groupBasic);
        }
        if ( !$groupContact ) {
            $groupContact = ProfilePropertyGroup::make('Contact Information', UserProfile::VISIBLE_PUBLIC);
            $manager->persist($groupContact);
        }
        if ( !$groupEducation ) {
            $groupEducation = ProfilePropertyGroup::make('Education Information', UserProfile::VISIBLE_PUBLIC);
            $manager->persist($groupEducation);
        }
        if ( !$groupSettings ) {
            $groupSettings = ProfilePropertyGroup::make('Additional Settings', UserProfile::VISIBLE_PUBLIC);
            $manager->persist($groupSettings);
        }

        $public = UserProfile::VISIBLE_PUBLIC;
        $private = UserProfile::VISIBLE_PRIVATE;
        $members = UserProfile::VISIBLE_MEMBERS;

        // required, visible (unused), default visibility, field type, field options
        $properties = [
            // Normal profile properties start here
            'IAm' => [false, true, $public, ProfileProperty::TYPE_TEXT,
                ['title' => 'Describe what you do in a few words']],
            'FirstName' => [true, true, $public, ProfileProperty::TYPE_TEXT, []],
            'LastName' => [true, true, $public, ProfileProperty::TYPE_TEXT, []],
            'BirthDay' => [true, false, $private, ProfileProperty::TYPE_DATE,
                ['date_years_rel' => '-18:-100']],
            'Gender' => [true, true, $public, ProfileProperty::TYPE_CHOICE,
                ['expanded' => true, 'choices' => ['m' => 'Male', 'f' => 'Female']]],
            'Address' => [false, false, $private, ProfileProperty::TYPE_TEXT, []],
            'Address2' => [false, false, $private, ProfileProperty::TYPE_TEXT, []],
            'City' => [true, false, $members, ProfileProperty::TYPE_TEXT, []],
            'State' => [true, false, $members, ProfileProperty::TYPE_TEXT, []],
            'PostalCode' => [true, false, $private, ProfileProperty::TYPE_TEXT, []],
            'Country' => [true, false, $members, ProfileProperty::TYPE_CHOICE, ['country' => true]],
            'Telephone' => [false, false, $private, ProfileProperty::TYPE_TEXT, []],
            'PhotoURL' => [false, false, $private, ProfileProperty::TYPE_FILE, []],
            'Major' => [true, false, $members, ProfileProperty::TYPE_TEXT, []],
            'HowIWouldUseScholarship' => [true, false, $members, ProfileProperty::TYPE_TEXTAREA, ['class' => 'maxlength', 'max_length' => 1000]],
            'ReceiveEmails' => [false, false, $private, ProfileProperty::TYPE_CHECKBOX, []],
            'HomePortal' => [false, false, $private, ProfileProperty::TYPE_TEXT, []],
            'SmartURL' => [false, false, $private, ProfileProperty::TYPE_TEXT, []],
            'PreferredLanguage' => [true, false, $private, ProfileProperty::TYPE_CHOICE,
                ['choices' => ['en' => 'English', 'es' => 'Spanish', 'pt' => 'Portuguese'],
                 'title' => 'Select your default website language']],
            'SchoolName' => [true, false, $members, ProfileProperty::TYPE_TEXT, []],
            'SchoolStatus' => [true, false, $members, ProfileProperty::TYPE_CHOICE, [
                'choices' => ['' => '', 'attending' => 'Attending', 'not_attending' => 'Not Attending', 'alumni' => 'Alumni'],
            ]],

            // Profile "Additional Settings" start here
            'SponsorVisibility' => [false, false, $private, ProfileProperty::TYPE_CHOICE, [
                'choices' => ['private' => 'Private', 'members_only' => 'Members Only', 'public' => 'Public'],
            ]],
        ];

        $propEntities = [];

        foreach ( $properties as $propName => $p ) {
            $existing = $manager->getRepository('GotChosenSiteBundle:ProfileProperty')
                ->findOneBy(['name' => $propName]);
            if ( $existing ) {
                $existing->setIsRequired($p[0]);
                $existing->setDefaultVisibility($p[2]);
                $existing->setFieldType($p[3]);
                $existing->setFieldOptions($p[4]);

                $propEntities[$propName] = $existing;
                continue;
            }

            $prop = new ProfileProperty();
            $prop->setName($propName);
            $prop->setIsRequired($p[0]);
            //$prop->setIsVisible($p[1]);
            $prop->setDefaultVisibility($p[2]);
            $prop->setFieldType($p[3]);
            $prop->setFieldOptions($p[4]);
            $manager->persist($prop);

            $propEntities[$propName] = $prop;
        }

        $manager->flush();

        // map groups
        // used to be $groupxxx->addProperty($propEntities['xxx']); but super regex replace from
        // intellij to the rescue!

        $this->addPropertyToGroup($manager, $groupBasic, $propEntities['FirstName']);
        $this->addPropertyToGroup($manager, $groupBasic, $propEntities['IAm']);
        $this->addPropertyToGroup($manager, $groupBasic, $propEntities['FirstName']);
        $this->addPropertyToGroup($manager, $groupBasic, $propEntities['LastName']);
        $this->addPropertyToGroup($manager, $groupBasic, $propEntities['BirthDay']);
        $this->addPropertyToGroup($manager, $groupBasic, $propEntities['Gender']);
        $this->addPropertyToGroup($manager, $groupBasic, $propEntities['PhotoURL']);
        $this->addPropertyToGroup($manager, $groupBasic, $propEntities['PreferredLanguage']);

        $this->addPropertyToGroup($manager, $groupContact, $propEntities['Address']);
        $this->addPropertyToGroup($manager, $groupContact, $propEntities['Address2']);
        $this->addPropertyToGroup($manager, $groupContact, $propEntities['City']);
        $this->addPropertyToGroup($manager, $groupContact, $propEntities['State']);
        $this->addPropertyToGroup($manager, $groupContact, $propEntities['PostalCode']);
        $this->addPropertyToGroup($manager, $groupContact, $propEntities['Country']);
        $this->addPropertyToGroup($manager, $groupContact, $propEntities['Telephone']);

        $this->addPropertyToGroup($manager, $groupEducation, $propEntities['Major']);
        $this->addPropertyToGroup($manager, $groupEducation, $propEntities['HowIWouldUseScholarship']);
        $this->addPropertyToGroup($manager, $groupEducation, $propEntities['SchoolName']);
        $this->addPropertyToGroup($manager, $groupEducation, $propEntities['SchoolStatus']);

        $this->addPropertyToGroup($manager, $groupSettings, $propEntities['SponsorVisibility']);

        $manager->flush();
    }

    private function addPropertyToGroup(ObjectManager $manager, ProfilePropertyGroup $group, ProfileProperty $property)
    {
        /** @var ProfileProperty $gprop */
        foreach ( $group->getProperties() as $gprop ) {
            if ( $gprop->getName() == $property->getName() ) {
                return;
            }
        }

        $group->addProperty($property);
    }
}
