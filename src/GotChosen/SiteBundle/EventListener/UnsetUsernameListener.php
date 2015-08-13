<?php
/**
 * Created by IntelliJ IDEA.
 * User: steven
 * Date: 9/27/13
 * Time: 4:38 PM
 * To change this template use File | Settings | File Templates.
 */

namespace GotChosen\SiteBundle\EventListener;

use GotChosen\SiteBundle\Controller\BaseController;
use GotChosen\SiteBundle\Controller\ProfileController;
use GotChosen\SiteBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class UnsetUsernameListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if ( !is_array($controller) ) {
            return;
        }
        
        $session = $event->getRequest()->getSession();

        /** @var BaseController $ctrl */
        $ctrl = $controller[0];
        if ( !is_object($ctrl) || !$ctrl instanceof BaseController ) {
            return;
        }

        // no loop for you, also allow username checking
        if ( $ctrl instanceof ProfileController
            && ($controller[1] == 'updateUsernameAction' || $controller[1] == 'checkUsernameAction') ) {
            return;
        }

        /** @var User $user */
        $user = $ctrl->getUser();
        if ( $user && $this->isGUID($user->getUsername()) ) {
            $session->getFlashBag()->add('error', "We recently changed our username restrictions. Your previous username is no longer valid. Please create a new one.");
            
            $url = $this->router->generate('reset_username');
            $event->setController(function() use ($url) {
                return new RedirectResponse($url);
            });
        }
    }

    public function isGUID($name)
    {
        return preg_match('/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/',
            strtoupper($name));
    }
}