<?php

namespace GotChosen\SiteBundle\Controller\Admin;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use GotChosen\SiteBundle\Controller\BaseController;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DrawingController
 * @package GotChosen\SiteBundle\Controller\Admin
 *
 * @Route(options={"i18n" = false})
 */
class IndexController extends BaseController
{
    /**
     * @Route("/admin/", name="admin_index")
     * @Route("/admin/home", name="admin_home")
     * @Template
     */
    public function homeAction()
    {
        $userRepo = $this->repo('User');
        $sshipRepo = $this->repo('Scholarship');
        $sshipEntryRepo = $this->repo('ScholarshipEntry');

        $currentMonthly = $sshipRepo->getCurrentMonthly();
        $current40K = $sshipRepo->getCurrent40K();

        if ( $currentMonthly ) {
            $currentMonthlyEntrants = $sshipEntryRepo->getNumEntrants($currentMonthly);
        } else {
            $currentMonthlyEntrants = 0;
        }

        if ( $current40K ) {
            $current40KEntrants = $sshipEntryRepo->getNumEntrants($current40K);
        } else {
            $current40KEntrants = 0;
        }

        $usersLastMonth = $userRepo->getNumUsersRegisteredLastMonth();
        $usersMonthToDate = $userRepo->getNumUsersRegisteredMonthToDate();

        $userCounts = $userRepo->getNumUsersByAllStatuses();

        // Update the status results to get their full names
        foreach ( $userCounts as &$counts ) {
            $counts['status'] = User::$status_types[$counts['status']];
        }

        return [
            'userCounts' => $userCounts,
            'usersLastMonth' => $usersLastMonth,
            'usersMonthToDate' => $usersMonthToDate,
            'currentMonthlyEntrants' => $currentMonthlyEntrants,
            'current40KEntrants' => $current40KEntrants,
        ];
    }

    /**
     * @Route("/admin/users", name="admin_users")
     * @Template
     */
    public function usersAction()
    {
        return [];
    }
}
