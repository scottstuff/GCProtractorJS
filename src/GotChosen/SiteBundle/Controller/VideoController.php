<?php

namespace GotChosen\SiteBundle\Controller;

use FOS\UserBundle\Model\UserManager;
use GotChosen\SiteBundle\Entity\VideoVote;
use GotChosen\SiteBundle\Entity\Video;
use GotChosen\SiteBundle\Entity\VideoStatus;
use GotChosen\SiteBundle\Entity\VideoCategory;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Repository\VideoRepository;
use GotChosen\SiteBundle\Repository\VideoCategoryRepository;
use GotChosen\SiteBundle\Repository\VideoVoteRepository;
use GotChosen\SiteBundle\Repository\VideoStatusRepository;
use GotChosen\Util\Dates;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class VideoController extends BaseController {

    //put your code here

    /**
     * @param Request $request
     * @return array
     *
     * @Route("/video-scholarship/videos", name="vs_videos")
     * @Template
     */
    public function videosAction(Request $request)
    {
        $category = $request->query->get('category');
        $videoTitle = $request->query->get('video_title');
        $email = $request->query->get('email');

        $filteredBy = "";

        /** @var VideoCategory $category */
        if ( $category ){

            $category = $this->repo('VideoCategory')->find($category);
            $filteredBy .= ", " . $category->getCategoryName();
        }
        else {
            $category = null; // Make sure this is null to prevent errors
        }

        if ( $videoTitle ) {
            $filteredBy .= ", {$videoTitle}";
        }

        if ( $email ) {
            $filteredBy .= ", {$email}";
        }

        $scholarship = $this->repo('Scholarship')->getCurrentVideo();
        if ( !$scholarship ){
        $scholarship = $this->repo('Scholarship')->find(16);
    }

        if ( !$scholarship ){
            $this->flash("warning", "There us currently no Video Scholarship contest running.");
            return $this->redirectRoute('video_scholarship');
        }

        /** @var Session $session */
        $session = $this->get('session');
        $page = $request->query->getDigits('page', 1);

        if ( !$session->has('videoSeed')) {
            $seed = microtime(true);
            $session->set('videoSeed', $seed);
        } else {
            $seed = $session->get('videoSeed');
        }


        /** @var VideoRepository $videoRepo */
        $videoRepo = $this->repo('Video');

        $total = $videoRepo->countVideosFiltered($scholarship, $videoTitle, $email, $category);//count($videos);

        $limit = 20;
        $numPages = max(1, ceil($total / $limit));

        if ( $category || $videoTitle || $email ){
            if ($page <= 1) {
                $page = 1;
            } else {
                $page = max(1, min($numPages, $page));
            }
        }
        else {
            $page = max(1, min($numPages, $page));
        }

        $offset = ($page - 1) * $limit;

        $minPage = max(1, $page - 2);
        $maxPage = min($numPages, $page + 2);

        //$videoCount = $videoRepo->countVideos($scholarship);

        $videos = $videoRepo->findVideosPaged($scholarship, $category, $videoTitle, $email, $seed, $offset, $limit);
        $sessionId = session_id();
        $votesRemaining = 0;

        $categories = $this->repo('VideoCategory')->findAll();
        foreach ($videos as $video) {

            $videoId = $video->getYoutubeURL();
            //$JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$videoId}?v=2&alt=json");
            //$JSON_Data = json_decode($JSON);
            //$views = $JSON_Data->{'entry'}->{'yt$statistics'}->{'viewCount'};
            //$video->setViews($views);
            $voteRepo = $this->repo('VideoVote');
            $votesRemaining = $voteRepo->getVotesRemaining($video, $request->getClientIp(), $sessionId, new \DateTime());
            $video->setVotesRemaining($votesRemaining);

        }

        $userHasVideo = false;
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.

        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $userHasVideo = $this->userHasVideo($user);
        }

        $filteredBy = trim($filteredBy, ", ");

        return [
            'videos' => $videos,
            'userHasVideo' => $userHasVideo,
            'votesRemaining' => $votesRemaining,
            'videoCount' => $total,
            'currentCategory' => $category,
            'categories' => $categories,
            'currentVideoTitle' => $videoTitle,
            'currentEmail' => $email,
            'filteredBy' => $filteredBy,

            'numPages' => $numPages,
            'page' => $page,
            'minPage' => $minPage,
            'maxPage' => $maxPage,
        ];
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     *
     * @Route("/video-scholarship/videos/{id}", name="vs_video")
     * @Template
     */
    public function videoAction(Request $request, $id)
    {
$scholarship = $this->repo('Scholarship')->getCurrentVideo();
if ( !$scholarship ){
$scholarship = $this->repo('Scholarship')->find(16);
}

        if ( !$scholarship ){
            $this->flash("warning", "There us currently no Video Scholarship contest running.");
            return $this->redirectRoute('video_scholarship');
        }

        /** @var User $user */
        $user = $this->getUser();

        $sessionId = session_id();


        $videoRepo = $this->repo('Video');
        $video = $videoRepo->find($id);
        if ($video->getStatus()->getId() > 1){
            $video = null;
        }

        $referer = $request->headers->get('referer');
        $fromTop20 = false;
        $fromGallery = false;
        if (strpos($referer, 'top20') !== false){
            $fromTop20 = true;
        }
        else
        {
            $fromGallery = true;
        }

        if ( !$video ){

            $this->flash('error', 'Video not found.');
            if ( $fromTop20 ) {
                return $this->redirectRoute('vs_top20');
            }
            else {
                return $this->redirectRoute('vs_videos');
            }
        }

        $videoUser = $video->getUser();
        if ( $videoUser->getStatus() != 'active') {
            $this->flash('error', 'Video not found.');
            if ( $fromTop20 ) {
                return $this->redirectRoute('vs_top20');
            }
            else {
                return $this->redirectRoute('vs_videos');
            }
        }

        $voteRepo = $this->repo('VideoVote');
        $votesRemaining = $voteRepo->getVotesRemaining($video, $request->getClientIp(), $sessionId, new \DateTime());
        $video->setVotesRemaining($votesRemaining);



        $fb = $this->createFormBuilder();
        $fb
            ->add('reportReason', 'choice', [
                'choices' => array(
                    'sexual_content' => 'Sexual content',
                    'violence' => 'Violent or repulsive content',
                    'hateful' => 'Hateful or abusive content',
                    'hate_speech' => 'Hate speech against a protected group',
                    'dangerous' => 'Harmful dangerous acts',
                    'child_endangerment' => 'Child endangerment or abuse',
                    'spam' => 'Spam or misleading',
                    'infringement' => 'Infringes my rights',
                    'impersonation' => 'Impersonation',
                    ),
                'empty_value' => 'Select a Reason',
                'empty_data' => null,
                'required' => true,
                'label' => 'Report this video for: ',
            ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ( $form->isValid() ) {

            $formReportReason = $form->get('reportReason')->getData();
            if ($formReportReason == null)
            {
                $form->get('reportReason')->addError(new FormError('You must select a reason in order to report a video.'));
                    return ['video' => $video,
                    'videoUser' => $videoUser,
                    'firstName' => $videoUser->getPropertyValue("FirstName", ''),
                    'votecount' => $video->getVoteCount(),
                    'fromTop20' => $fromTop20,
                    'fromGallery' => $fromGallery,
                    'form' => $form->createView(),
                ];
            }
            else
            {
                $reason = $formReportReason;
                if ( $reason ) {
                        $body = 'This video: https://www.gotchosen.com/en/video-scholarship/videos/' . $id . ' has been reported for the following reason: ' . $reason . '.';

                        $msg = \Swift_Message::newInstance(
                        'GotChosen: Video Reported by User.')
                        ->setFrom("noreply@gotchosen.com", 'GotChosen - automated message, do not reply')
                        ->setTo('support@gotchosen.com')
                        ->setBody($body, 'text/plain');
                        $this->mailer()->send($msg);

                        $this->flash('success', "This video has been reported.");
                }

            }
        }
        $activeScholarship = $this->repo('Scholarship')->getCurrentVideo();

        if ( !$activeScholarship ){
            $video->setVotesRemaining(0);
        }



        return ['video' => $video,
                'videoUser' => $videoUser,
                'firstName' => $videoUser->getPropertyValue("FirstName", ''),
                'votecount' => $video->getVoteCount(),
                'fromTop20' => $fromTop20,
                'fromGallery' => $fromGallery,
                'isActive' => $scholarshipActive,
                'form' => $form->createView(),
            ];

    }

    /**
     * @param Request $request
     * @return array
     *
     * @Route("/video-scholarship/top20", name="vs_top20")
     * @Template
     */
    public function top20Action(Request $request)
    {
$scholarship = $this->repo('Scholarship')->getCurrentVideo();
if ( !$scholarship ){
$scholarship = $this->repo('Scholarship')->find(16);
}


        if ( !$scholarship ){
            $this->flash("warning", "There us currently no Video Scholarship contest running.");
            return $this->redirectRoute('video_scholarship');
        }
        /** @var VideoRepository $gameRepo */
        $videoRepo = $this->repo('Video');
        $videos = $videoRepo->findTop20Videos($scholarship);
        $sessionId = session_id();

        $votesRemaining = 0;
        foreach ($videos as $video) {

            $videoId = $video->getYoutubeURL();
            //$JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$videoId}?v=2&alt=json");
            //$JSON_Data = json_decode($JSON);
            //$views = $JSON_Data->{'entry'}->{'yt$statistics'}->{'viewCount'};
            //$video->setViews($views);
            $voteRepo = $this->repo('VideoVote');
            $votesRemaining = $voteRepo->getVotesRemaining($video, $request->getClientIp(), $sessionId, new \DateTime());
            $video->setVotesRemaining($votesRemaining);

        }

        $userHasVideo = false;
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.

        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $userHasVideo = $this->userHasVideo($user);
        }

        $keys = array_keys($videos);

        shuffle($keys);
        $new = [];
        foreach($keys as $key) {
            $new[$key] = $videos[$key];
        }

        $videos = $new;
        return [
            'videos' => $videos,
            'userHasVideo' => $userHasVideo,
            'votesRemaining' => $votesRemaining
        ];
    }



    /**
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/video-scholarship/vote/{id}", name="video_vote")
     * @Method("POST")
     */
    public function voteAction(Request $request, $id)
    {
        $ip = $request->getClientIp();

        /** @var VideoVotesRepository $voteRepo */
        $voteRepo = $this->repo('VideoVote');

        /** @var Videos $game */
        $video = $this->repo('Video')->find($id);

        if ( $video === null ) {
            return $this->renderJson([
                'status' => 'error',
                'message' => 'Video not found',
            ]);
        }
        $sessionId = session_id();

        if ( !$voteRepo->canVoteOnVideo($video, $ip, $sessionId, new \DateTime()) ) {
            return $this->renderJson([
                'status' => 'error',
                'message' => 'You have reached your vote limit for today',
            ]);
        }

        $remaining = $voteRepo->getVotesRemaining($video, $ip, $sessionId, new \DateTime());

        $vote = VideoVote::make($video, $ip, $sessionId);
        $this->em()->persist($vote);

        $this->em()->flush();

        return $this->renderJson([
            'status' => 'ok',
            'votesRemaining' => $remaining - 1,
        ]);
    }

    /**
     * @param Request $request
     * @throws AccessDeniedException
     * @return array
     *
     * @Route("/video-scholarship/submit", name="vs_submit")
     * @Template
     */
    public function submitAction(Request $request)
    {
        $sship = $this->repo('Scholarship')->getCurrentVideo();

        if ( !$sship ){
            $this->flash("warning", "There us currently no Video Scholarship contest running.");
            return $this->redirectRoute('video_scholarship');
        }

        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {
            $this->flash('error', 'You must be registered and logged in to submit a video entry.');
            throw new AccessDeniedException();
        }

        $video = $this->repo('Video')->findOneBy(['user' => $user->getId()]);

        if ( $video ) {
            return $this->redirectRoute('vs_manage');
        }
        else{
            $sship = $this->repo('Scholarship')->getCurrentVideo();
            if ($user->hasApplied($sship))
            {

            }
            else
            {
                //$this->flash('error', 'You must provide the required information in order to apply for the video scholarship and submit a video entry.');
                //$absURL = $this->get('router')->generate('scholarship_apply', array('id' => $sship->getId()), true);
                //return $this->redirect($absURL);
                return ['sship' => $sship];
            }
        }

        $fb = $this->createFormBuilder();
        $fb
            ->add('videoTitle', 'text', [
                'label' => 'Video Title',
                'constraints' => [new NotBlank()],
            ])
            ->add('videoCategory', 'entity', [
                'class' => 'GotChosenSiteBundle:VideoCategory',
                'empty_value' => 'Pick a Category',
                'empty_data' => null,
                'required' => true,
                'property' => 'categoryName'
            ])
            ->add('youtubeURL', 'text', [
                'label' => 'YoutubeURL',
                'constraints' => [new NotBlank()],
            ])
            ->add('accept', 'checkbox', [
                'label' => 'I Accept the Rules',
                'widget_checkbox_label' => 'label',
                'error_type' => 'block',
                'constraints' => [new NotBlank()],
            ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

            $youtubeId = '';
            // Check to see if it is a valid Youtube URL.
            $youtubeURL = trim($form->get('youtubeURL')->getData());
            if ( strpos($youtubeURL, 'youtube.com') ===false && strpos($youtubeURL, 'youtu.be.com') === false)
            {
                if ($form->isValid())
                {
                    $form->get('youtubeURL')->addError(new FormError('You did not enter a valid Youtube URL.'));
                }

            }


            // Check what type of Youtube URL it is.
            $urlType = '';
            $ytTemp = strstr($youtubeURL, 'v=');
            if (strlen($ytTemp) > 0)
            {
                // Is a standard Youtube address such as: https://www.youtube.com/watch?v=ZcekUuC7D9Y
                // Now that we know the type, we can extract the Video ID.
                $youtubeId = substr($ytTemp,2, 11);
            }
            $ytTemp = strstr($youtubeURL, '/v/');
            if (strlen($ytTemp) > 0)
            {
                // Is an old embed Youtube address such as: https://www.youtube.com/v/ZcekUuC7D9Y?hl=en_US&amp;version=3
                // Now that we know the type, we can extract the Video ID.
                $youtubeId = substr($ytTemp,3, 11);
            }
            $ytTemp = strstr($youtubeURL, '/embed/');
            if (strlen($ytTemp) > 0)
            {
                // Is a new embed Youtube address such as: https://www.youtube.com/embed/ZcekUuC7D9Y
                // Now that we know the type, we can extract the Video ID.
                $youtubeId = substr($ytTemp,7, 11);
            }

            if ( strlen($youtubeId) > 0)
            {
                // Looks like we have a good Id to validate.
                $JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$youtubeId}?v=2&alt=json");
                $JSON_Data = json_decode($JSON);
                $videoState = $JSON_Data->{'entry'}->{'app:control'}->{'yt:state'};
                if (strlen($videoState) > 0)
                {
                    // Video is not playable.  No good.
                if ($form->isValid())
                {

                    $form->get('youtubeURL')->addError(new FormError('There is something wrong with the video you submitted.  It is either restricted, deleted or still processing.'));
                }

                }
                else
                {
                    // Video is loaded, playable and was not rejected or removed.
                }
            }
            else
            {
                // Not a good Youtube URL.
                if ($form->isValid())
                {
                    $form->get('youtubeURL')->addError(new FormError('You did not enter a valid Youtube URL.'));
                }

            }

        if ( $form->isValid() ) {

            $video = new Video();
            $video->setUser($user);

            $scholarship = $this->repo('Scholarship')->getCurrentVideo();
            $video->setScholarship($scholarship);

            $formCategoryData = $form->get('videoCategory')->getData();
            if ($formCategoryData == null)
            {
                $form->get('videoCategory')->addError(new FormError('You must pick a category.'));
                return [
                  'form' => $form->createView(),
                ];
            }

            $category = $this->repo('VideoCategory')->findOneBy(['id' => $formCategoryData]);
            $video->setCategory($category);

            $status = $this->repo('VideoStatus')->findOneBy(['id' => 1]);
            $video->setStatus($status);

            $video->setTitle(($form->get('videoTitle')->getData()));

            $video->setYoutubeURL($youtubeId);

            $video->setDTAdded(new \DateTime('now'));

            $this->em()->persist($video);
            $this->em()->flush();

            $this->flash('success', "Your video entry was submitted successfully.");

            return $this->redirectRoute('vs_manage');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     * @throws AccessDeniedException
     * @return array
     *
     * @Route("/video-scholarship/manage", name="vs_manage")
     * @Template
     */
    public function manageAction(Request $request)
    {
        $sship = $this->repo('Scholarship')->getCurrentVideo();

        if ( !$sship ){
            $this->flash("warning", "There us currently no Video Scholarship contest running.");
            return $this->redirectRoute('video_scholarship');
        }

        $userHasVideo = false;
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.
            $this->flash('error', 'You must be registered and logged in to submit a video entry.');
            throw new AccessDeniedException();


        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $userHasVideo = $this->userHasVideo($user);
        }

        $videoRepo = $this->repo('Video');
        $video = $videoRepo->findOneBy(['user' => $user->getId()]);
        $videoId = $video->getYoutubeURL();
        $JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$videoId}?v=2&alt=json");
        $JSON_Data = json_decode($JSON);
        $views = $JSON_Data->{'entry'}->{'yt$statistics'}->{'viewCount'};
        $video->setViews($views);

        return ['userHasVideo' => $userHasVideo,
                'video' => $video,
                'votecount' => $video->getVoteCount(),];

    }

    /**
     * @return array
     *
     * @Route("/video-scholarship/rules", name="vs_rules")
     * @Template
     */
    public function rulesAction()
    {
        $userHasVideo = false;
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.

        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $userHasVideo = $this->userHasVideo($user);
        }

        return ['userHasVideo' => $userHasVideo];
    }

    /**
     *
     * @return array
     * @Route("/video-scholarship/faq", name="vs_faq")
     * @Template
     */
    public function faqAction()
    {
        $userHasVideo = false;
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.

        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $userHasVideo = $this->userHasVideo($user);
        }

        return ['userHasVideo' => $userHasVideo];
    }

    /**
     * @return array
     * @Route("/video-scholarship", name="video_scholarship")
     * @Template
     */
    public function videoScholarshipAction()
    {
        $userHasVideo = false;
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.

        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $userHasVideo = $this->userHasVideo($user);
        }

        $category = null;
        $videoTitle = '';
        $email = '';

$scholarship = $this->repo('Scholarship')->getCurrentVideo();
if ( !$scholarship ){
$scholarship = $this->repo('Scholarship')->find(16);
}


        $seed = microtime(true);


        /** @var VideoRepository $videoRepo */
        $videoRepo = $this->repo('Video');

        $limit = 7;
        $offset = 0;
        if ( !$scholarship ){
            $videos = null;
        }
        else {
            $videos = $videoRepo->findVideosPaged($scholarship, $category, $videoTitle, $email, $seed, $offset, $limit);
        }


        return [
            'videos' => $videos,
            'userHasVideo' => $userHasVideo,

        ];

    }

       /**
     * @return array
     * @Route("/video-scholarship", name="vs_about")
     * @Template
     */
    public function aboutAction()
    {
        $userHasVideo = false;
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.

        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $userHasVideo = $this->userHasVideo($user);
        }

        return ['userHasVideo' => $userHasVideo];
    }
    /**
     * @return array
     *
     * @Route("/video-scholarship/pastseasons", name="vs_pastseasons")
     * @Template
     */
    public function pastseasonsAction()
    {
        $userHasVideo = false;
        /** @var User $user */
        $user = $this->getUser();
        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.

        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $userHasVideo = $this->userHasVideo($user);
        }

        return ['userHasVideo' => $userHasVideo];
    }

    /**
     * @return boolean
     */
    private function userHasVideo($user)
    {
        $hasVideo = false;

        if ( $user === null || !$user->hasRole('ROLE_USER') ) {

            //not logged in.

        }
        else
        {
            // Check to see if user has a video and is in the current scholarship.
            $scholarship = $this->repo('Scholarship')->getCurrentVideo();
            if ( !$scholarship ) {
                return false;
            }

            $video = $this->repo('Video')->findOneBy(['scholarship' => $scholarship->getId(), 'user' => $user->getId()]);
            if ( $video )
            {
                $hasVideo = true;
            }
        }

        return $hasVideo;
    }
}
