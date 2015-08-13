<?php
/**
 * Created by IntelliJ IDEA.
 * User: steven
 * Date: 9/23/13
 * Time: 6:16 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GotChosen\SiteBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Entity\NotificationCommType;
use GotChosen\SiteBundle\Entity\NotificationType;

class LoadNotificationTypes implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        $typeRepo = $manager->getRepository('GotChosenSiteBundle:NotificationCommType');

        $general = $typeRepo->findOneBy(['typeName' => 'General Notifications']);
        if ( !$general ) {
            $general = new NotificationCommType();
            $general->setTypeName('General Notifications');
            $manager->persist($general);
        }

        $eg = $typeRepo->findOneBy(['typeName' => 'Evolution Games Notifications']);
        if ( !$eg ) {
            $eg = new NotificationCommType();
            $eg->setTypeName('Evolution Games Notifications');
            $manager->persist($eg);
        }

        $manager->flush();

        $notificationTypes = [
            ['Newsletters', $general, true],
            ['Scholarship Information', $general, false],
            ['Sponsor Notifications', $general, false],

            ['Developer Feedback Notifications', $eg, false],
            ['Scholarship Notifications', $eg, false],
            ['EG News', $eg, false],
        ];

        $repo = $manager->getRepository('GotChosenSiteBundle:NotificationType');

        foreach ( $notificationTypes as $nt ) {
            if ( !$repo->findOneBy(['name' => $nt[0]]) ) {
                $type = new NotificationType();
                $type->setName($nt[0]);
                $type->setCommType($nt[1]);
                $type->setIsDefault($nt[2]);
                $manager->persist($type);
            }
        }

        $manager->flush();
    }
}