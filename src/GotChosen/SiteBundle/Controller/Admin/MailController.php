<?php

namespace GotChosen\SiteBundle\Controller\Admin;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserManager;
use GotChosen\Mail\Filter;
use GotChosen\SiteBundle\Controller\BaseController;
use GotChosen\SiteBundle\Entity\MassMailQueue;
use GotChosen\SiteBundle\Entity\NotificationType;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\MassMailQueueRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/*
Template: dropdown, maybe initially just pull a file list from a directory
Subject: text input
Body: CKEditor or tinyMCE, most likely, for HTML editing. CKEditor dev docs are nicer, may be biased towards that.
(future: add more fields depending on chosen template's placeholders)
(future, or now: add plain textarea for a text-only MIME alternative, or generate based on HTML - which could
be a pain to throw together quickly)

Is Preview: checkbox, default checked

## if preview checked
Preview e-mail: text input, target of the preview e-mail
(future: expand to textarea, one e-mail per line?)

## else
Language: dropdown, [ALL, en, es, pt]
Notification Type: dropdown, [ALL, etc.] If one selected, add link to e-mail footer to user_unsubscribe route,
type=x, email=Strings::base64EncodeUrl(addr). If ALL selected, just link to newsletter settings or nothing?
Should be rare to pick ALL here.
Scholarships: multi-select, active scholarships, will only send to users that are signed up for at least 1
of the selected scholarships. no filtering if none selected.
Has Submitted Game: checkbox, only sends to users that submitted a game to EG.

If the Language or Notification Type fields are set to ALL, show a red alert div above the Send button, disable
the button, and maybe have a checkbox inside the warning that you need to check to enable the button?

For Send, in preview mode it can just stay right on the page using Symfony to keep the form data filled in, with
a flash message saying the preview was sent. In normal mode it can redirect out to the archive list.

Archive list should be something simple, just a table listing the MassMailQueue records, subject, date sent,
status, maybe current position/total/approx percentage.
*/

/**
 * Class MailController
 * @package GotChosen\SiteBundle\Controller\Admin
 *
 * @Route(options={"i18n" = false})
 */
class MailController extends BaseController
{
    /**
     * View list of queue entries.
     * @return array
     *
     * @Route("/admin/mail", name="admin_mail")
     * @Template
     */
    public function archiveAction()
    {
        /** @var MassMailQueueRepository $mmRepo */
        $mmRepo = $this->repo('MassMailQueue');

        $queue = $mmRepo->findBy([], ['dateAdded' => 'DESC']);

        return [
            'queue' => $queue,
        ];
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/admin/mail/pause/{id}", name="admin_mail_pause")
     */
    public function pauseAction($id)
    {
        $this->updateStatus($id, MassMailQueue::STATUS_PAUSED);
        return $this->redirectRoute('admin_mail');
    }

    /**
     * @param $id
     * @return RedirectResponse
     *
     * @Route("/admin/mail/resume/{id}", name="admin_mail_resume")
     */
    public function resumeAction($id)
    {
        $this->updateStatus($id, MassMailQueue::STATUS_RESUMING);
        return $this->redirectRoute('admin_mail');
    }

    private function updateStatus($id, $status)
    {
        $mmRepo = $this->repo('MassMailQueue');

        /** @var MassMailQueue $queue */
        $queue = $mmRepo->find($id);
        $queue->setStatus($status);
        $this->em()->flush();
    }

    /**
     * Create and submit a mass mail entry.
     * @param Request $request
     * @return array
     *
     * @Route("/admin/mail/create", name="admin_mail_create")
     * @Template
     */
    public function createAction(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $templates = $this->getTemplates();
        $ntypes = $this->getNotificationTypes();

        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        $formData = ['isPreview' => true, 'previewEmail' => $currentUser->getEmail()];
        $fb = $this->createFormBuilder($formData);

        $fb
            ->add('template', 'choice', [
                'choices' => $templates,
            ])
            ->add('subject', 'text')
            ->add('body', 'textarea')
            ->add('isPreview', 'checkbox', [
                'required' => false,
                'label' => 'Is Preview?',
                'render_optional_text' => false,
                'widget_checkbox_label' => 'label',
            ])
            ->add('previewEmail', 'email', [
                'required' => false,
                'label' => 'Preview E-mail',
                'render_optional_text' => false,
            ])
            ->add('language', 'choice', [
                'choices' => ['ANY' => '--- ANY ---', 'en' => 'English', 'es' => 'Spanish', 'pt' => 'Portuguese'],
            ])
            ->add('notificationType', 'choice', [
                'choices' => $ntypes,
                'label' => 'Notification Type',
            ])
            ->add('userStatus', 'choice', [
                'choices' => User::$status_types,
                'label' => 'User Status',
            ])
            ->add('scholarships', 'entity', [
                'class' => 'GotChosenSiteBundle:Scholarship',
                'property' => 'scholarshipName',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.startDate < :now')
                        ->andWhere('s.endDate > :now')
                        ->setParameter('now', new \DateTime());
                },
                'multiple' => true,
                'required' => false,
                'render_optional_text' => false,
                'expanded' => true,
            ])
            ->add('hasSubmittedGame', 'checkbox', [
                'required' => false,
                'label' => 'Has Submitted Game?',
                'render_optional_text' => false,
                'widget_checkbox_label' => 'label',
            ]);

        $form = $fb->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            if ( $form->get('isPreview')->getData() ) {
                // -------------------
                // Send preview
                //

                /** @var User $user */
                $user = $userManager->findUserByEmail($form->get('previewEmail')->getData());

                if ( $user ) {
                    $entry = MassMailQueue::makePreview($user,
                        $form->get('template')->getData(),
                        $form->get('subject')->getData(),
                        ['body' => $form->get('body')->getData()]
                    );

                    $this->em()->persist($entry);
                    $this->em()->flush();

                    $this->flash('success', 'Preview message queued for delivery');
                } else {
                    $this->flash('error', 'User for given e-mail address not found');
                }
            } else {
                // -------------------
                // Send for real
                //

                $filter = new Filter();

                $lang = $form->get('language')->getData();
                if ( $lang != 'ANY' ) {
                    $filter->setLanguage($lang);
                }

                $ntId = $form->get('notificationType')->getData();
                if ( $ntId != 0 ) {
                    /** @var NotificationType $nt */
                    $nt = $this->repo('NotificationType')->find($form->get('notificationType')->getData());
                    $filter->setNotificationType($nt);
                }

                $status = $form->get('userStatus')->getData();
                $filter->setUserStatus($status);

                $sslist = $form->get('scholarships')->getData();
                if ( !empty($sslist) ) {
                    $filter->setScholarships($sslist);
                }

                if ( $form->get('hasSubmittedGame')->getData() ) {
                    $filter->setHasSubmittedGame(true);
                }

                $entry = MassMailQueue::makeFiltered(
                    $filter,
                    $form->get('template')->getData(),
                    $form->get('subject')->getData(),
                    ['body' => $form->get('body')->getData()]
                );

                $this->em()->persist($entry);
                $this->em()->flush();

                $this->flash('success', 'Message queued for delivery');
                return $this->redirectRoute('admin_mail');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function getTemplates()
    {
        $templates = [];
        $finder = new Finder();
        $files = $finder->files()->in(GC_PROJECT_ROOT . '/src/GotChosen/SiteBundle/Resources/views/Newsletters');
        /** @var \SplFileInfo $f */
        foreach ( $files as $f ) {
            $name = str_replace('.html.twig', '', $f->getFilename());
            $templates[$f->getFilename()] = $name;
        }

        return $templates;
    }

    private function getNotificationTypes()
    {
        /** @var NotificationType[] $types */
        $types = $this->repo('NotificationType')->findBy([], ['name' => 'ASC']);
        $options = [0 => '--- IGNORE ---'];
        foreach ( $types as $type ) {
            $options[$type->getId()] = $type->getName();
        }

        return $options;
    }
}
