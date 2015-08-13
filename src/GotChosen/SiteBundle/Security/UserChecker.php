<?php

namespace GotChosen\SiteBundle\Security;

use GotChosen\SiteBundle\Entity\User;
use GotChosen\Util\Strings;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserChecker as BaseUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends BaseUserChecker
{
    public function checkPreAuth(UserInterface $user)
    {
        if ( !$user instanceof AdvancedUserInterface ) {
            return;
        }

        if ( !$user->isCredentialsNonExpired() ) {
            $ex = new CredentialsExpiredException('NEEDS_RESET');
            $ex->setUser($user);
            throw $ex;
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof AdvancedUserInterface) {
            return;
        }

        if (!$user->isAccountNonLocked()) {
            $ex = new LockedException('User account is locked.');
            $ex->setUser($user);
            throw $ex;
        }

        if ( !$user->isEnabled() and $user->getStatus() == User::STATUS_BAD_EMAIL ) {
            $ex = new DisabledException('BAD_EMAIL');
            $ex->setUser($user);
            throw $ex;
        }

        if ( !$user->isEnabled() ) {
            $ex = new DisabledException('DISABLED');
            if ( $user instanceof User && $user->getConfirmationToken() ) {
                $ex = new DisabledException('DISABLED:' . Strings::base64EncodeUrl($user->getEmail()));
            }

            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->isAccountNonExpired()) {
            $ex = new AccountExpiredException('User account has expired.');
            $ex->setUser($user);
            throw $ex;
        }
    }
}
