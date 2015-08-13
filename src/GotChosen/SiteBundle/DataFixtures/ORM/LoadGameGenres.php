<?php

namespace GotChosen\SiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GotChosen\SiteBundle\Entity\EGGameGenre;


class LoadGameGenres implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        $genres = [
            'Action',
            'Adventure & RPG',
            'Learning & Tutorials',
            'Music',
            'Puzzle',
            'Shooter',
            'Sports & Racing',
            'Strategy & Defense',
            'Other'
        ];
        
        $repo = $manager->getRepository('GotChosenSiteBundle:EGGameGenre');
        
        foreach ( $genres as $genre ) {
            if ( !$repo->findOneBy(['name' => $genre]) ) {
                $o = new EGGameGenre();
                $o->setName($genre);
                $manager->persist($o);
            }
        }
        
        $manager->flush();
    }
}
