<?php

namespace GotChosen\SiteBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use GotChosen\SiteBundle\Entity\AuditLog;
use GotChosen\SiteBundle\Entity\PasswordLog;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\GCSiteEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthAuditListener implements LogoutHandlerInterface, EventSubscriberInterface
{
    private $doctrine;
    private $em;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        if ( $token !== null && ($user = $token->getUser()) ) {
            $audit = AuditLog::make($user, 'login', $event->getRequest()->getClientIp());
            $this->em->persist($audit);
            $this->em->flush();
        }
    }

    public function onChangePasswordCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        if ( $user instanceof User ) {
            $audit = AuditLog::make($user, 'password-changed', $event->getRequest()->getClientIp());
            $this->em->persist($audit);
            $this->em->flush();
        }
    }

    public function onResettingResetSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        if ( $user instanceof User ) {
            $audit = AuditLog::make($user, 'password-reset', $event->getRequest()->getClientIp());
            $this->em->persist($audit);

            $plog = PasswordLog::make($user, $event->getRequest()->getClientIp());
            $this->em->persist($plog);
            $this->em->flush();
        }
    }

    public function onUserAccountDisabled(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        if ( $user instanceof User ) {
            $audit = AuditLog::make($user, 'account-deleted', $event->getRequest()->getClientIp());
            $this->em->persist($audit);
            $this->em->flush();
        }
    }

    /**
     * This method is called by the LogoutListener when a user has requested
     * to be logged out. Usually, you would unset session variables, or remove
     * cookies, etc.
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if ( $token !== null && ($user = $token->getUser()) ) {
            $audit = AuditLog::make($user, 'logout', $request->getClientIp());
            $this->em->persist($audit);
            $this->em->flush();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            FOSUserEvents::CHANGE_PASSWORD_COMPLETED => 'onChangePasswordCompleted',
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'onResettingResetSuccess',
            GCSiteEvents::USER_ACCOUNT_DISABLED => 'onUserAccountDisabled',
        ];
    }
}