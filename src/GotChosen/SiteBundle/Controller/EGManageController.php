<?php

namespace GotChosen\SiteBundle\Controller;

use GotChosen\SiteBundle\Entity\EGFeedback;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\EGFeedbackRepository;
use GotChosen\SiteBundle\Repository\EGGameStatsRepository;
use GotChosen\SiteBundle\Repository\EGPlayerStatsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GotChosen\SiteBundle\Entity\EGGame;
use GotChosen\SiteBundle\Entity\EGPlaySession;
use GotChosen\Util\Upload\GameUploader;
use GotChosen\Util\Upload\PhotoUploader;
use GotChosen\Util\MimeType\UnityMimeTypeGuesser;

class EGManageController extends BaseController
{
    /**
     * @param Request $request
     * @throws AccessDeniedException
     * @return array
     *
     * @Route("/evolution-games/submit", name="eg_submit")
     * @Template
     */
    public function submitAction(Request $request)
    {
        return $this->redirectRoute('eg_scholarship');
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {
            $this->flash('error', 'You must be registered and logged in to submit a game.');
            throw new AccessDeniedException();
        }

        $game = $this->repo('EGGame')->findOneBy(['user' => $user->getId()]);

        if ( $game ) {
            return $this->redirectRoute('eg_manage');
        }

        // studio name, studio profile, game synopsis, game name, screenshot, avatar, upload game, terms checkbox

        $fb = $this->createFormBuilder();
        $fb
            ->add('studioName', 'text', [
                'label' => 'Studio Name',
                'constraints' => [new NotBlank()],
            ])
            ->add('studioProfile', 'textarea', [
                'label' => 'Studio Profile',
                'constraints' => [new NotBlank()],
            ])
            ->add('gameSynopsis', 'textarea', [
                'label' => 'Game Synopsis',
                'constraints' => [new NotBlank()],
            ])
            ->add('gameName', 'text', [
                'label' => 'Game Name',
                'constraints' => [new NotBlank()],
            ])
            ->add('gameGenre', 'entity', [
                'class' => 'GotChosenSiteBundle:EGGameGenre',
                'property' => 'name'
            ])
            ->add('accept', 'checkbox', [
                'label' => 'I Accept the Rules',
                'widget_checkbox_label' => 'label',
                'error_type' => 'block',
                'constraints' => [new NotBlank()],
            ]);

        $form = $fb->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            /**
             * Todo:
             *
             * 1. Can't use existing game names?
             * 2. Can't use existing studio names?
             */
            $game = new EGGame();
            $game->setUser($user);
            $game->setStudioName($form->get('studioName')->getData());
            $game->setStudioProfile($form->get('studioProfile')->getData());
            $game->setGameSynopsis($form->get('gameSynopsis')->getData());
            $game->setGameName($form->get('gameName')->getData());
            $game->setGenre($form->get('gameGenre')->getData());

            $this->em()->persist($game);
            $this->em()->flush();

            $this->flash('success', "Your game was submitted successfully.");

            return $this->redirectRoute('eg_manage');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Route("/evolution-games/submit-gate", name="eg_submit_gate")
     * @Template
     */
    public function submitGateAction()
    {
        // We're pretty much removing all of this page's logic. Oh, well.
        return $this->redirectRoute('eg_scholarship');

        return [];
    }

    /**
     * @param Request $request
     * @throws AccessDeniedException
     * @return array
     *
     * @Route("/evolution-games/manage", name="eg_manage")
     * @Template
     */
    public function manageAction(Request $request)
    {
        return $this->redirectRoute('eg_scholarship');

        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {
            $this->flash('error', 'You must be registered and logged in to submit a game.');
            throw new AccessDeniedException();
        }

        $game = $this->repo('EGGame')->findOneBy(['user' => $user->getId()]);

        if ( !$game ) {
            return $this->redirectRoute('eg_submit_gate');
        }

        /**
         * Do this somewhere else?
         */
        $guesser = MimeTypeGuesser::getInstance();
        $guesser->register(new UnityMimeTypeGuesser());

        $feedback = $this->repo('EGFeedback')->fetchFeedbackForGame($game);
        $stats = $this->repo('EGGameStats')->getOrCreate($game, date('Y-m'));
        $scholarship = $this->repo('Scholarship')->getCurrentEvoGames();

        $phase = 'qualifier';
        if ( $this->repo('EGGame')->isInContest($game, $scholarship) ) {
            $phase = 'contest';
        }

        $fb = $this->createFormBuilder();
        $fb->add('game', 'file', [
            'label' => ( $game->getSwfFile() == null ? 'Upload Game: ' : 'Re-upload Game: ' ),
            'constraints' => [new NotBlank()],
        ]);
        $form = $fb->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            $filesystem = $this->fs('game');
            $gameUploader = new GameUploader($filesystem);

            try {
                $file = $form->get('game')->getData();
                $url = $gameUploader->upload($file);
                $game->setSwfFile($url);

                switch ( $file->getMimeType() ) {
                    case 'application/vnd.unity':
                        $game->setType(EGGame::TYPE_UNITY);
                        break;

                    case 'application/x-shockwave-flash':
                        $game->setType(EGGame::TYPE_FLASH);
                        break;

                    default:
                        $game->setType(EGGame::TYPE_FLASH);
                        break;
                }

                $this->em()->flush();

                /**
                 * TODO: Clean up old files in S3.
                 */
            }
            catch(\InvalidArgumentException $e) {
                $this->flash('error', $e->getMessage());
                return $this->redirectRoute('eg_manage');
            }

            $this->flash('success', "Your file was uploaded successfully.");
            return $this->redirectRoute('eg_manage');
        }

        $gameParameters = [];
        $flashVars = [];

        if ( $game->getSwfFile() != null ) {
            // Create a play session for testing
            /** @var SecureRandomInterface $random */
            $random = $this->get('security.secure_random');
            $session = EGPlaySession::make($random, EGPlaySession::PHASE_FREEPLAY, $game, $user);
            $this->em()->persist($session);
            $this->em()->flush();

            $gameParameters = [
                'session_id' => $session ? $session->getId() : null,
                'completion_url' => $this->generateUrl('eg_manage', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'api_endpoint' => (
                        strpos($this->getRequest()->getHttpHost(), 'gotchosen.dev') === false
                            ? 'https://'
                            : 'http://'
                        ) . $this->getRequest()->getHttpHost() . '/evolution-games/api/v1'
            ];

            $flashVars = http_build_query([
                'session_id' => $gameParameters['session_id'],
                'completion_url' => $gameParameters['completion_url'],
                'api_endpoint' => $gameParameters['api_endpoint']
            ]);
        }

        return [
            'game' => $game,
            'gameStatus' => EGGame::$status_types[$game->getStatus()],
            'flashVars' => $flashVars,
            'gameParameters' => $gameParameters,
            'monthStats' => $stats,
            'feedback' => $feedback,
            'phase' => $phase,
            'submitRateUrl' => $this->generateUrl('eg_manage_rate_feedback'),
            'form' => $form->createView(),
        ];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/manage/edit", name="eg_edit")
     * @Secure(roles="ROLE_USER")
     * @Template
     */
    public function editAction(Request $request)
    {
        return $this->redirectRoute('eg_scholarship');

        $user = $this->getUser();
        $game = $this->repo('EGGame')->findOneBy(['user' => $user->getId()]);

        if ( !$game ) {
            return $this->redirectRoute('eg_submit_gate');
        }

        $fb = $this->createFormBuilder();
        $fb
            ->add('studioName', 'text', [
                'label' => 'Studio Name',
                'constraints' => [new NotBlank()],
            ])
            ->add('studioProfile', 'textarea', [
                'label' => 'Studio Profile',
                'constraints' => [new NotBlank()],
            ])
            ->add('gameSynopsis', 'textarea', [
                'label' => 'Game Synopsis',
                'constraints' => [new NotBlank()],
            ])
            ->add('gameName', 'text', [
                'label' => 'Game Name',
                'constraints' => [new NotBlank()],
            ])
            ->add('gameGenre', 'entity', [
                'class' => 'GotChosenSiteBundle:EGGameGenre',
                'property' => 'name'
            ]);

        $fb->setData([
            'studioName' => $game->getStudioName(),
            'studioProfile' => $game->getStudioProfile(),
            'gameSynopsis' => $game->getGameSynopsis(),
            'gameName' => $game->getGameName(),
            'gameGenre' => $game->getGenre()
        ]);

        $form = $fb->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            /**
             * Todo:
             *
             * 1. Can't use existing game names?
             * 2. Can't use existing studio names?
             */
            $game->setStudioName($form->get('studioName')->getData());
            $game->setStudioProfile($form->get('studioProfile')->getData());
            $game->setGameSynopsis($form->get('gameSynopsis')->getData());
            $game->setGameName($form->get('gameName')->getData());
            $game->setGenre($form->get('gameGenre')->getData());

            $this->em()->flush();

            $this->flash('success', "Your game was updated successfully.");

            return $this->redirectRoute('eg_manage');
        }

        return ['form' => $form->createView()];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/manage/edit/avatar", name="eg_edit_avatar")
     * @Secure(roles="ROLE_USER")
     * @Template
     */
    public function editAvatarAction(Request $request)
    {
        return $this->redirectRoute('eg_scholarship');

        $user = $this->getUser();
        $game = $this->repo('EGGame')->findOneBy(['user' => $user->getId()]);

        if ( !$game ) {
            return $this->redirectRoute('eg_submit_gate');
        }

        $fb = $this->createFormBuilder();
        $fb->add('studioAvatar', 'file', [
                'label' => 'Studio Avatar',
                'constraints' => [new NotBlank()],
            ]);

        $form = $fb->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            $avatarFs = $this->fs('avatar');
            $avatarUpload = new PhotoUploader($avatarFs);

            try {
                $url = $avatarUpload->upload($form->get('studioAvatar')->getData());
                $game->setAvatarFile($url);

                /**
                 * TODO: Clean up old files in S3.
                 * TODO: Filesize/dimensions limiting?
                 */
            } catch (\InvalidArgumentException $e) {
                $this->flash('error', $e->getMessage());
                return $this->redirectRoute('eg_edit_avatar');
            }

            $this->em()->flush();

            $this->flash('success', "Your game was updated successfully.");
            return $this->redirectRoute('eg_manage');
        }

        return ['form' => $form->createView(), 'currentAvatar' => $game->getAvatarFile()];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/manage/edit/screenshot", name="eg_edit_screenshot")
     * @Secure(roles="ROLE_USER")
     * @Template
     */
    public function editScreenshotAction(Request $request)
    {
        return $this->redirectRoute('eg_scholarship');

        $user = $this->getUser();
        $game = $this->repo('EGGame')->findOneBy(['user' => $user->getId()]);

        if ( !$game ) {
            return $this->redirectRoute('eg_submit_gate');
        }

        $fb = $this->createFormBuilder();
        $fb->add('gameScreenshot', 'file', [
                'label' => 'Game Screenshot (maximum 2MB, minimum 300x200)',
                'constraints' => [
                    new NotBlank(),
                    new File(['maxSize' => '2M', 'mimeTypes' => 'image/*']),
                    new Image(['minWidth' => 300, 'minHeight' => 200]),
                ],
            ]);

        $form = $fb->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            $screenshotFs = $this->fs('screenshot');
            $screenshotUpload = new PhotoUploader($screenshotFs);

            try {
                $url = $screenshotUpload->upload($form->get('gameScreenshot')->getData());
                $game->setScreenshotFile($url);

                /**
                 * TODO: Clean up old files in S3.
                 * TODO: Filesize/dimensions limiting?
                 */
            } catch (\InvalidArgumentException $e) {
                $this->flash('error', $e->getMessage());
                return $this->redirectRoute('eg_edit_screenshot');
            }

            $this->em()->flush();

            $this->flash('success', "Your game was updated successfully.");
            return $this->redirectRoute('eg_manage');
        }

        return ['form' => $form->createView(), 'currentScreenshot' => $game->getScreenshotFile()];
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/evolution-games/manage/rate-feedback", name="eg_manage_rate_feedback")
     * @Secure(roles="ROLE_USER")
     */
    public function rateFeedbackAction(Request $request)
    {
        return $this->redirectRoute('eg_scholarship');

        $fbId = $request->request->getInt('feedback_id');
        $rating = $request->request->getInt('rating', -1);

        if ( !in_array($rating, [0, 1, 3, 5]) ) {
            return $this->renderJson(['status' => 'error', 'error' => 'Invalid rating']);
        }

        if ( $fbId == 0 ) {
            return $this->renderJson(['status' => 'error', 'error' => 'Feedback entry not found']);
        }

        // make sure it's for our game
        /** @var EGFeedback $feedback */
        $feedback = $this->repo('EGFeedback')->find($fbId);
        if ( !$feedback || $feedback->getGame()->getUser()->getId() !== $this->getUser()->getId() ) {
            return $this->renderJson(['status' => 'error', 'error' => 'Feedback entry not found']);
        }

        // not rated before
        if ( $feedback->getRatedDate() !== null ) {
            return $this->renderJson(['status' => 'error', 'error' => 'You have already rated this feedback']);
        }

        $ratedDate = new \DateTime('now');
        $feedback->setDeveloperRating($rating);
        $feedback->setRatedDate($ratedDate);

        // recalculate user's feedback points here, if this is their first, or they had a previous rating
        // less than the one we just gave them, or they have 100 feedback points.

        $sship = $this->repo('Scholarship')->getCurrentEvoGames(false);

        /** @var EGPlayerStatsRepository $statsRepo */
        $statsRepo = $this->repo('EGPlayerStats');
        /** @var EGFeedbackRepository $fbRepo */
        $fbRepo = $this->repo('EGFeedback');
        /** @var EGGameStatsRepository $gsRepo */
        $gsRepo = $this->repo('EGGameStats');

        if ( $sship ) {
            $pstats = $statsRepo->getOrCreate($feedback->getUser(), $sship, $feedback->getCreatedDate()->format('Y-m'));
            $pstats->setFeedbacksRated($pstats->getFeedbacksRated() + 1);
        }

        $highestFeedback = $fbRepo->fetchHighestFeedback($feedback->getGame(), $feedback->getUser(),
            $feedback->getCreatedDate()->format('Y-m'));

        $gstats = $gsRepo->getOrCreate($feedback->getGame(), $feedback->getCreatedDate()->format('Y-m'));
        $gstats->setMonthRatedFeedbacks($gstats->getMonthRatedFeedbacks() + 1);
        $feedback->getGame()->setTotalRatedFeedbacks($feedback->getGame()->getTotalRatedFeedbacks());

        // get the highest feedback before flushing, so the latest doesn't pollute the data.
        // but flush before recalculation, when the up-to-date points actually matter.
        $this->em()->flush();

        if ( $sship && $pstats->getFeedbackPoints() < 100 && $rating > $highestFeedback ) {
            $fbRepo->recalcFeedbackPoints($feedback->getUser(), $sship, $feedback->getCreatedDate()->format('Y-m'));
        }

        return $this->renderJson(['status' => 'ok', 'date' => $ratedDate->format('n/j/Y @ g:i A')]);
    }

    /**
     * @return string
     */
    private function generateSecureKey()
    {
        $random = $this->get('security.secure_random');
        return base64_encode($random->nextBytes(48));
    }
}
