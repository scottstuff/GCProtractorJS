<?php

namespace GotChosen\SiteBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class SecurityController
 * @package GotChosen\SiteBundle\Controller
 */
class SecurityController extends BaseSecurityController
{
	 /**
     * login
     *
     * @Route("/login", name="login_override")
     * @Template
     */
	public function loginAction(Request $request)
    {
        $currentDate = new \DateTime;
        $cutoffDate = new \DateTime("2014-10-01 00:00:00");
        if ($currentDate >= $cutoffDate)
        {
            $route = $this->container->get('router')->generate('login-disabled');
            return new RedirectResponse($route);
        }
        else
        {
            $response = parent::loginAction($request);
        }
    	
        return $response;
    }
}