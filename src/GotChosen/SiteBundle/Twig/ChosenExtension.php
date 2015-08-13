<?php

namespace GotChosen\SiteBundle\Twig;

use GotChosen\SiteBundle\Entity\User;
use GotChosen\Util\Strings;
use Doctrine\ORM\EntityManager;

class ChosenExtension extends \Twig_Extension
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('gc_autosuper', [$this, 'autoSuper'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('gc_slugify', [$this, 'slugify'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('gc_userHasGame', [$this, 'userHasGame']),
            new \Twig_SimpleFunction('gc_egContestActive', [$this, 'egContestActive']),
            new \Twig_SimpleFunction('gc_egQualifierCount', [$this, 'egQualifierCount']),
            new \Twig_SimpleFunction('gc_userProfileProperty', [$this, 'userProfileProperty'])
        ];
    }

    public function autoSuper($text)
    {
        return preg_replace('/(\d+)(st|nd|rd|th)/i', '$1<sup>$2</sup>', $text);
    }

    public function userHasGame(User $user)
    {
        $gameRepo = $this->em->getRepository('GotChosenSiteBundle:EGGame');

        $game = $gameRepo->findOneBy(['user' => $user->getId()]);

        return !is_null($game);
    }

    public function slugify($string)
    {
        return Strings::slugify($string);
    }

    public function egContestActive()
    {
        $egScholarship = $this->em->getRepository('GotChosenSiteBundle:Scholarship')->getCurrentEvoGames();

        return $egScholarship->getId() != 0;
    }

    /**
     * Returns the count of Qualified Games.
     *
     * @return integer count of qualified games.
     */
    public function egQualifierCount()
    {
        $egScholarship = $this->em->getRepository('GotChosenSiteBundle:Scholarship')->getCurrentEvoGames();

        $totalQuailifiedGames = $this->em->getRepository('GotChosenSiteBundle:EGGame')->countQualifierGames($egScholarship);

        return $totalQuailifiedGames;
    }

    /**
    *
    * Returns the value of a user's profile property.
    * It can optionally respect visibility settings.
    *
    * @return string profile property value
    */
    public function userProfileProperty(User $user, $propName, $currentUser, $respectVisibility = true)
    {
        $prop = $this->em->getRepository('GotChosenSiteBundle:User')->getProfileProperty($user, $propName);

        if ( !$prop ) {
            return;
        }

        if ($respectVisibility)
        {
            $viewerNetworks = [];
            // no need to do this if we're viewing our own profile
            if ( $currentUser && $currentUser->getId() != $user->getId() ) {
                foreach ( $currentUser->getNetworks() as $netmap ) {
                    $viewerNetworks[] = $netmap->getNetwork()->getId();
                }
            }

            if ($prop->isVisibleBy($currentUser, $viewerNetworks))
            {
                return $prop->getPropertyValue();
            }
        }
        else
        {
            return $prop->getPropertyValue();
        }
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'gotchosen';
    }
}
