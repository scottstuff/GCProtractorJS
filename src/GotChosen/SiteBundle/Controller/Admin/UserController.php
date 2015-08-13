<?php

namespace GotChosen\SiteBundle\Controller\Admin;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserManager;
use GotChosen\SiteBundle\Controller\BaseController;
use GotChosen\SiteBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 * @package GotChosen\SiteBundle\Controller\Admin
 *
 * @Route(options={"i18n" = false})
 */
class UserController extends BaseController
{
    const USERS_PER_PAGE = 10;

    /**
     * @Route("/admin/users/{page}", requirements={"page" = "\d+"}, name="admin_users")
     * @Template
     */
    public function indexAction(Request $request, $page = 1)
    {
        $searchTerm = $request->query->get('search');
        $userRepo = $this->repo('User');

        $offset = self::USERS_PER_PAGE * ($page - 1);

        if (!empty($searchTerm))
        {
            $numPages = ceil($userRepo->getNumUsersByAdminSearch($searchTerm) / self::USERS_PER_PAGE);
            $users = $userRepo->findUsersByAdminSearch($searchTerm,
                                self::USERS_PER_PAGE,
                                $offset);
        }
        else
        {
            $numPages = ceil($userRepo->getNumUsers() / self::USERS_PER_PAGE);
            $users = $userRepo->findBy([], ['username' => 'ASC'],
                        self::USERS_PER_PAGE,
                        $offset);
        }

        return [
            'users' => $users,
            'page' => $page,
            'numPages' => $numPages,
            'searchTerm' => $searchTerm,
        ];
    }

    /**
     * @Route("/admin/users/enable/{username}", requirements={"username" = "\w+"}, name="admin_users_enable")
     */
    public function enableAction(Request $request, $username)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);

        if ($user)
        {
            if (!$user->isEnabled())
            {
                $user->setEnabled(true);
                $user->setStatus(User::STATUS_ACTIVE);
                $userManager->updateUser($user);
            }
            $this->flash('success', 'User <strong>' . $user->getUsername() . '</strong> has been enabled.');
        }
        else
        {
            $this->flash('error', 'User <strong>' . $username . '</strong> not found.');
        }

        return $this->redirectRoute('admin_users', $request->query->all());
    }

    /**
     * @Route("/admin/users/disable/{username}", requirements={"username" = "\w+"}, name="admin_users_disable")
     */
    public function disableAction(Request $request, $username)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);

        if ($user)
        {
            if ($user->isEnabled())
            {
                $user->setEnabled(false);
                $user->setStatus(User::STATUS_DISABLED_ADMIN);
                $userManager->updateUser($user);
            }

            $this->flash('success', 'User <strong>' . $user->getUsername() . '</strong> has been disabled.');
        }
        else
        {
            $this->flash('error', 'User <strong>' . $username . '</strong> not found.');
        }

        return $this->redirectRoute('admin_users', $request->query->all());
    }

    /**
     * @Route("/admin/users/unsubscribe/{username}", requirements={"username" = "\w+"}, name="admin_users_unsubscribe")
     */
    public function unsubscribeAction(Request $request, $username)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);

        if ($user)
        {
            $subs = $user->getNotificationSubs();
            foreach ($subs as $sub)
            {
                if ($user->hasNotificationSub($sub))
                {
                    $user->removeNotificationSub($sub);
                    $this->em()->remove($sub);
                }
            }
            $userManager->updateUser($user);
            $this->flash('success', 'User <strong>' . $user->getUsername() . '</strong> has been unsubscribed.');
        }
        else
        {
            $this->flash('error', 'User <strong>' . $username . '</strong> not found.');
        }

        return $this->redirectRoute('admin_users', $request->query->all());
    }
}
