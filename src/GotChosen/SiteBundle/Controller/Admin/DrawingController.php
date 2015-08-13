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
class DrawingController extends BaseController
{
    /**
     * @Route("/admin/drawing", name="admin_drawing")
     * @Template
     */
    public function drawingAction()
    {
        /**
         * 1. Fetch list of scholarships of type 40k or monthly
         * 2. Sort them in DESC order.
         * 3. Create drawing link based on the id of the scholarship.
         */
        $scholarships = $this->repo('Scholarship')->getAll40kAndMonthly();
        
        return ['scholarships' => $scholarships];
    }
    
    /**
     * @Route("/admin/drawing/complete/{scholarshipId}", name="admin_drawing_complete")
     */
    public function drawingComplete(Request $request, $scholarshipId)
    {
        $scholarship = $this->repo('Scholarship')->find($scholarshipId);
        
        $scholarship->setDrawingComplete(true);
        
        $this->em()->flush();
        
        $this->flash('success', "Drawing for '" . $scholarship->getScholarshipName() . "' marked as complete.");
        
        return $this->redirectRoute('admin_drawing');
    }
    
    /**
     * @Route("/admin/drawing/40k/{scholarshipId}", name="admin_drawing_40k")
     * @Template
     */
    public function drawing40KAction(Request $request, $scholarshipId)
    {
        /** @var Scholarship $scholarship **/
        $scholarship = $this->repo('Scholarship')->find($scholarshipId);
        
        if ( !$scholarship or $scholarship->getScholarshipType() != Scholarship::TYPE_40K ) {
            $this->flash('error', 'Something went wrong. Contact a developer.');
            return $this->redirectRoute('admin_drawing');
        }
        
        return ['scholarshipId' => $scholarshipId, 'scholarship' => $scholarship];
    }

    /**
     * @Route("/admin/drawing/monthly/{scholarshipId}", name="admin_drawing_monthly")
     * @Template
     */
    public function drawingMonthlyAction(Request $request, $scholarshipId)
    {
        /** @var Scholarship $scholarship **/
        $scholarship = $this->repo('Scholarship')->find($scholarshipId);
        
        if ( !$scholarship or $scholarship->getScholarshipType() != Scholarship::TYPE_MONTHLY ) {
            $this->flash('error', 'Something went wrong. Contact a developer.');
            return $this->redirectRoute('admin_drawing');
        }
        
        return ['scholarshipId' => $scholarshipId, 'scholarship' => $scholarship];
    }

    /**
     * @Route("/admin/drawing/40k/pick/{scholarshipId}", name="admin_drawing_40k_pick")
     */
    public function pick40KAction(Request $request, $scholarshipId)
    {
        /** @var Scholarship $scholarship **/
        $scholarship = $this->repo('Scholarship')->find($scholarshipId);
        
        if ( !$scholarship or $scholarship->getScholarshipType() != Scholarship::TYPE_40K ) {
            return $this->renderJson(['error' => 'Incorrect scholarship type.']);
        }

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        // count all sponsors
        $stmt = $conn->executeQuery(
            'SELECT COUNT(*) AS ct FROM EntrySponsors es
             JOIN Entries e ON (e.id = es.entryId)
             JOIN User u ON (u.id = e.idUser)
             WHERE e.idScholarship = ' . $scholarshipId . ' AND u.enabled = 1');
        $sponsorCount = (int) $stmt->fetchColumn();

        // count all entries
        $stmt = $conn->executeQuery(
            'SELECT COUNT(*) AS ct FROM Entries e
             JOIN User u ON (u.id = e.idUser)
             WHERE e.idScholarship = ' . $scholarshipId . ' AND u.enabled = 1');
        $entryCount = (int) $stmt->fetchColumn();

        $random = $this->reallyRandomNumber(0, $sponsorCount + $entryCount - 1);

        // big ass list of user ids to entry counts
        $stmt = $conn->executeQuery(
            'SELECT e.idUser, (1 + COUNT(*)) AS ct FROM EntrySponsors es
             JOIN Entries e ON (e.id = es.entryId and e.idScholarship = ' . $scholarshipId . ')
             JOIN User u ON (u.id = e.idUser)
             WHERE u.enabled = 1
             GROUP BY e.idUser

             UNION SELECT e.idUser, 1 FROM Entries e
             JOIN User u ON (u.id = e.idUser)
             WHERE e.idScholarship = ' . $scholarshipId . '
                 AND e.id NOT IN (SELECT entryId FROM EntrySponsors)
                 AND u.enabled = 1');

        $winner = 0;
        while ( $row = $stmt->fetch(\PDO::FETCH_NUM) ) {
            if ( $random < $row[1] ) {
                $winner = $row[0];
                break;
            }
            $random -= $row[1];
        }

        $stmt->closeCursor();

        $user = $this->repo('User')->find($winner);
        $this->sendEmail($scholarship, $user);

        return $this->renderJson(['userId' => $winner]);

        /** @var User $winningUser */
//        $winningUser = $this->repo('User')->find($winner);
//        if ( $winningUser ) {
//            /*return $this->renderJson([
//                'name' => $winningUser->getPropertyValue('LastName') . ', '
//                          . $winningUser->getPropertyValue('FirstName')
//            ]);*/
//        }
//
//        return $this->renderJson(['name' => '']);
    }

    /**
     * @Route("/admin/drawing/monthly/pick/{scholarshipId}", name="admin_drawing_monthly_pick")
     */
    public function pickMonthlyAction(Request $request, $scholarshipId)
    {
        /** @var Scholarship $scholarship **/
        $scholarship = $this->repo('Scholarship')->find($scholarshipId);
        
        if ( !$scholarship or $scholarship->getScholarshipType() != Scholarship::TYPE_MONTHLY ) {
            return $this->renderJson(['error' => 'Incorrect scholarship type.']);
        }

        /** @var Connection $conn */
        $conn = $this->getDoctrine()->getConnection();

        // count all entries
        $stmt = $conn->executeQuery(
            'SELECT COUNT(*) AS ct FROM Entries e
             JOIN User u ON (u.id = e.idUser)
             WHERE e.idScholarship = ' . $scholarshipId . ' AND u.enabled = 1');
        $entryCount = (int) $stmt->fetchColumn();

        $random = $this->reallyRandomNumber(0, $entryCount - 1);

        // easier here, we can just jump into a random offset of the result set.
        $stmt = $conn->executeQuery(
            'SELECT e.idUser FROM Entries e
             JOIN User u ON (u.id = e.idUser)
             WHERE e.idScholarship = ' . $scholarshipId . ' AND u.enabled = 1
             LIMIT ' . $random . ', 1');

        $row = $stmt->fetch(\PDO::FETCH_NUM);
        $winner = $row[0];

        $user = $this->repo('User')->find($winner);
        $this->sendEmail($scholarship, $user);

        return $this->renderJson(['userId' => $winner]);
    }

    /**
     * @Route("/admin/drawing/fullname", name="admin_drawing_fullname")
     */
    public function getFullNameAction(Request $request)
    {
        $id = $request->query->get('id');

        $winner = $this->repo('User')->find($id);
        if ( $winner ) {
            return $this->renderJson([
                'name' => $winner->getPropertyValue('LastName') . ', ' . $winner->getPropertyValue('FirstName')
            ]);
        }
        return $this->renderJson(['name' => 'Not Found']);
    }

    protected function sendEmail(Scholarship $sship, User $user)
    {
        $params = ['scholarship' => $sship, 'user' => $user];

        $msg = \Swift_Message::newInstance('GotChosen Scholarship Winner')
            ->setFrom('info@gotchosen.com')
            ->setTo('drawing@gotchosen.com')
            ->setBody($this->renderView('GotChosenSiteBundle:Emails:drawing_winner.txt.twig', $params), 'text/plain');
        $this->mailer()->send($msg);
    }

    protected function reallyRandomNumber($min, $max)
    {
        $url = "http://www.random.org/integers/?num=1&min=$min&max=$max&col=1&base=10&rnd=new&format=plain";
        return (int) file_get_contents($url);
    }
}