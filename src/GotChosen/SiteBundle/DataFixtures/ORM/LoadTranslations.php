<?php

namespace GotChosen\SiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Repository\TranslationRepository;

class LoadTranslations implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        $enProps = [
            'IAm' => 'I am',
            'FirstName' => 'First Name',
            'LastName' => 'Last Name',
            'Gender' => 'Gender',
            'BirthDay' => 'Birthday',
            'Address' => 'Address',
            'Address2' => 'Address Line 2',
            'City' => 'City',
            'State' => 'State',
            'PostalCode' => 'Zip/Postal Code',
            'Country' => 'Country',
            'Telephone' => 'Phone Number',
            'PhotoURL' => 'Photo',
            'Major' => 'Major',
            'HowIWouldUseScholarship' => 'How I Would Use the Scholarship',
            'ReceiveEmails' => 'Receive E-mails',
            'HomePortal' => 'Home Portal',
            'SmartURL' => 'Smart URL',
            'PreferredLanguage' => 'Preferred Language',
            'SchoolName' => 'School Name',
            'SchoolStatus' => 'School Status',
            'SponsorVisibility' => 'Sponsor List Privacy',
        ];

        /** @var TranslationRepository $repo */
        $repo = $manager->getRepository('GotChosenSiteBundle:Translation');

        foreach ( $enProps as $enKey => $enText ) {
            $repo->save('en', 'profile_properties', 'profile_properties.' . $enKey, $enText);
        }

        $manager->flush();
    }
}
