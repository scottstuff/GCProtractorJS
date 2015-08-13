<?php

namespace GotChosen\SiteBundle\Controller;

use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use GotChosen\SiteBundle\Entity\NotificationSub;
use GotChosen\SiteBundle\Entity\NotificationType;
use GotChosen\SiteBundle\Entity\PasswordLog;
use GotChosen\SiteBundle\Entity\ProfileProperty;
use GotChosen\SiteBundle\Entity\ProfilePropertyGroup;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Entity\UserProfile;
use GotChosen\SiteBundle\GCSiteEvents;
use GotChosen\SiteBundle\Repository\EGPlayerStatsRepository;
use GotChosen\SiteBundle\Repository\UserRepository;
use GotChosen\SiteBundle\Validator\Constraints\ReservedWords;
use GotChosen\User\ReportCard;
use GotChosen\User\UserPropertyHandler;
use GotChosen\Util\Strings;
use GotChosen\SiteBundle\Entity\EntrySponsor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class ProfileController
 *
 * @package GotChosen\SiteBundle\Controller
 */
class ProfileController extends BaseController
{
    /**
     * @Route("/profile/", name="fos_user_profile_show")
     * @Route("/profile/me", name="user_my_profile")
     */
    public function meAction()
    {
        $user = $this->getUser();

        if ( $user ) {
            return $this->redirectRoute('user_profile', ['username' => $user->getUsername()]);
        } else {
            return $this->redirectRoute('home');
        }
    }

    /**
     * ProfileViewer.aspx?userid=xxx
     * ProfileViewer/tabid/88/userId/xxx/Default.aspx
     *
     * @Route("/profile/{username}", name="user_profile")
     * @Route("/profile-id/{username}", name="user_profile_id")
     * @Template
     */
    public function indexAction(Request $request, $username)
    {
        $route = $request->attributes->get('_route');

        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        /** @var User $user */
        if ( $route == 'user_profile' ) {
            $user = $userManager->findUserByUsername($username);

            // We're probably not supposed to do this, but ...
            if ( !$user ) {
                $user = $userManager->findUserBy(['username' => $username]);
            }
        } else {
            $user = $userManager->findUserBy(['id' => $username]);
        }

        if ( !$user || !$user->isEnabled() ) {
            throw new NotFoundHttpException('User not found');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $viewerNetworks = [];
        // no need to do this if we're viewing our own profile
        if ( $currentUser && $currentUser->getId() != $user->getId() ) {
            foreach ( $currentUser->getNetworks() as $netmap ) {
                $viewerNetworks[] = $netmap->getNetwork()->getId();
            }
        }

        // the getProperties(User) method requires far less queries than just doing $user->getProfile()
        // since it joins with ProfileProperty and ProfilePropertiesInNetworks already.

        /** @var UserPropertyHandler $transformer */
        $transformer = $this->get('gotchosen.user_property_handler');

        /** @var UserProfile[] $properties */
        $properties = $this->repo('User')->getProperties($user);
        $viewableProps = [];
        $isVisible = [];
        foreach ( $properties as $property ) {
            $pname = $property->getProperty()->getName();
            $pvalue = $property->getPropertyValue();
            if ( !empty($pvalue) && $property->isVisibleBy($currentUser, $viewerNetworks) ) {
                $viewableProps[$pname] = $transformer->transformToView($property->getProperty(), $pvalue);
                $isVisible[$pname] = true;
            } else {
                $isVisible[$pname] = false;
            }
        }

        if ( !isset($viewableProps['PhotoURL']) ) {
            $viewableProps['PhotoURL'] = '';
            $isVisible['PhotoURL'] = false;
        }

        // New profile stuff in the following block. This is ugly and I don't
        // really care. I'm just making it work for now.
        //
        // 1. Compress some properties together
        // 2. Stuff properties into profile groups
        {
            $fullIAm = sprintf("%s %s %s",
                    isset($viewableProps['FirstName']) ? $viewableProps['FirstName'] : '',
                    isset($viewableProps['LastName']) ? $viewableProps['LastName'] : '',
                    isset($viewableProps['IAm']) ? "| " . $viewableProps['IAm'] : '');

            if ( isset($viewableProps['AddressLine1'])
                    or isset($viewableProps['AddressLine2'])
                    or isset($viewableProps['City'])
                    or isset($viewableProps['State'])
                    or isset($viewableProps['State'])
                    or isset($viewableProps['Zipcode'])
                    or isset($viewableProps['Country']) )
            {
                $fullAddress = sprintf("%s %s %s %s %s\n%s",
                        isset($viewableProps['Address']) ? $viewableProps['Address'] . "\n" : '',
                        isset($viewableProps['Address2']) ? $viewableProps['Address2'] . "\n" : '',
                        isset($viewableProps['City']) ? $viewableProps['City'] . ',' : '',
                        isset($viewableProps['State']) ? $viewableProps['State'] : '',
                        isset($viewableProps['PostalCode']) ? $viewableProps['PostalCode'] : '',
                        isset($viewableProps['Country']) ? $viewableProps['Country'] : '');
            }

            $viewablePropGroups['About Me']['IAm'] = $fullIAm;

            foreach ( ['BirthDay', 'Gender'] as $name ) {
                if ( isset($viewableProps[$name]) ) {
                    $viewablePropGroups['About Me'][$name] = $viewableProps[$name];
                }
            }

            if ( isset($viewableProps['Telephone']) ) {
                $viewablePropGroups['Contact Information']['Telephone'] = $viewableProps['Telephone'];
            }

            if ( isset($fullAddress) ) {
                $viewablePropGroups['Contact Information']['Address'] = $fullAddress;
            }

            foreach ( ['SchoolName', 'Major', 'SchoolStatus', 'HowIWouldUseScholarship'] as $name ) {
                if ( isset($viewableProps[$name]) ) {
                    $viewablePropGroups['Education Information'][$name] = $viewableProps[$name];
                }
            }
        }

        $scholarships = $this->repo('Scholarship')->getCurrentScholarships();

        $sship40k = $this->repo('Scholarship')->getCurrent40K();
        $sponsorCount = 0;
        $sponsoring = [];
        $currentIsSponsoring = false;
        $showSponsors = false;

        if ( $sship40k ) {
            $entry40k = $user->getScholarshipEntry($sship40k);
            if ( $entry40k ) {
                $sponsorCount = $this->repo('ScholarshipEntry')->countSponsors($entry40k);
                if ( $currentUser ) {
                    $currentSponsoring = $this->repo('ScholarshipEntry')->getSponsoring($currentUser, $sship40k);
                    $currentIsSponsoring = count($currentSponsoring) > 0;
                }
            }

            $sponsoring = $this->repo('ScholarshipEntry')->getSponsoring($user, $sship40k);
        }

        if ( $user->getPropertyValue('SponsorVisibility') == 'public' or ( $currentUser
                and $user->getPropertyValue('SponsorVisibility') == 'members_only' ) ) {
            $showSponsors = true;
        }

        $video = null;

        $voteCount = 0;
        $returnVideo = null;
        // need to do this if we're viewing our own profile
        if ( $currentUser && $currentUser->getId() == $user->getId() )
        {
            $sshipVideo = $this->repo('Scholarship')->getCurrentVideo();
            if ( $sshipVideo )
            {
               if ($currentUser->hasApplied($sshipVideo))
               {
                    $video = $this->repo('Video')->findOneBy(['scholarship' => $sshipVideo->getId(), 'user' => $user->getId()]);
                        if ( $video )
                        {
                            $voteCount = $video->getVoteCount();
                            $returnVideo = $video;
                            $videoId = $video->getYoutubeURL();
                            $JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$videoId}?v=2&alt=json");
                            $JSON_Data = json_decode($JSON);
                            $views = $JSON_Data->{'entry'}->{'yt$statistics'}->{'viewCount'};
                            $returnVideo->setViews($views);
                        }
                }
            }
        }
        else //Do this for public profile viewers.
        {
            $sshipVideo = $this->repo('Scholarship')->getCurrentVideo();
            if ( $sshipVideo )
            {
               if ($user->hasApplied($sshipVideo))
               {
                    $video = $this->repo('Video')->findOneBy(['scholarship' => $sshipVideo->getId(), 'user' => $user->getId()]);
                    if ( $video )
                    {
                        $returnVideo = $video;
                        $videoId = $video->getYoutubeURL();
                        $JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$videoId}?v=2&alt=json");
                        $JSON_Data = json_decode($JSON);
                        $views = $JSON_Data->{'entry'}->{'yt$statistics'}->{'viewCount'};
                        $returnVideo->setViews($views);

                    }
                }
            }
        }

        // set up photo url form
        if ( $currentUser && $currentUser->getId() == $user->getId() ) {
            list($photoForm, $fileInfos) = $this->getPhotoForm($user);
        } else {
            $photoForm = false;
            $fileInfos = false;
        }

        /** @var EGPlayerStatsRepository $statsRepo */
        $statsRepo = $this->repo('EGPlayerStats');
        /** @var Scholarship $egScholarship */
        $egScholarship = $this->repo('Scholarship')->getCurrentEvoGames();

        /** @var ReportCard $reportCard */
        $reportCard = $this->get('gotchosen.report_card_manager')->getForUser($user);

        return [
            'user' => $user,
            'properties' => $viewableProps,
            'propGroups' => $viewablePropGroups,
            'isVisible' => $isVisible,
            'scholarships' => $scholarships,
            'sponsorCount' => $sponsorCount,
            'sponsoring' => $sponsoring,
            'showSponsors' => $showSponsors,
            'currentIsSponsoring' => $currentIsSponsoring,
            'scholarship' => $sship40k,

            'reportCard' => $reportCard,

            'votecount' => $voteCount,
            'video' => $returnVideo,

            'photoForm' => ( $photoForm ? $photoForm->createView() : null ),
            'fileInfos' => $fileInfos,
        ];
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/_profile/update-photo", name="user_profile_update_photo")
     */
    public function updatePhotoAction(Request $request)
    {
        $user = $this->getUser();
        list($photoForm, $fileInfos) = $this->getPhotoForm($user);

        $photoForm->handleRequest($request);

        if ( $photoForm->isValid() ) {
            /** @var ProfileProperty $propRef */
            $propRef = $this->repo('User')->getPropertyReference('PhotoURL');
            $pname = $propRef->getName();

            /** @var UserPropertyHandler $transformer */
            $transformer = $this->get('gotchosen.user_property_handler');

            $this->repo('User')->setPropertyHandler($transformer);
            $this->repo('User')->setProperty($user, $pname,
                $transformer->transformToData($propRef, $photoForm->get($pname)->getData()),
                $photoForm->has("privacy_$pname") ? $photoForm->get("privacy_$pname")->getData() : null);

            $this->em()->flush();

            $this->flash('success', 'Your photo has been updated.');
            $this->get('session')->save();
        }

        return $this->redirectRoute('user_profile', ['username' => $user->getUsername()]);
    }

    private function getPhotoForm(User $user)
    {
        $builder = $this->createNamedFormBuilder('photo');

        /** @var ProfileProperty $photoProp */
        $photoProp = $this->repo('User')->getPropertyReference('PhotoURL');
        $photoProp->createFormElement($builder, true);
        $pname = 'PhotoURL';

        /** @var UserProfile $userProp */
        $userProp = $this->repo('User')->getProfileProperty($user, 'PhotoURL');
        $fileInfos = [];
        $formData = [];

        if ( !$photoProp->shouldHidePrivacyControls() ) {
            $builder->add("privacy_$pname", 'choice', [
                'choices' => [
                    UserProfile::VISIBLE_PUBLIC => 'Everyone',
                    UserProfile::VISIBLE_PRIVATE => 'Private',
                    UserProfile::VISIBLE_MEMBERS => 'Members Only',
                ],
                'label' => 'Visible to...',
            ]);

            if ( $userProp ) {
                $formData["privacy_$pname"] = $userProp->getVisibility();
                $fileInfos[$pname] = $userProp->getPropertyValue();
            } else {
                $formData["privacy_$pname"] = $photoProp->getDefaultVisibility();
                $fileInfos[$pname] = '';
            }
        }

        $builder->setData($formData);

        return [$builder->getForm(), $fileInfos];
    }

    /**
     * @param Request $request
     * @param $username
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/profile/{username}/sponsors", name="user_profile_sponsors")
     * @Template
     */
    public function sponsorListAction(Request $request, $username)
    {
        $maxPerPage = 15;

        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        /** @var User $user */
        $user = $userManager->findUserByUsername($username);

        if ( !$user || !$user->isEnabled() ) {
            throw new NotFoundHttpException('User not found');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ( $user->getPropertyValue('SponsorVisibility') != 'public' ) {
            if ( !$currentUser or $currentUser and $user->getId() != $currentUser->getId()
                    and $user->getPropertyValue('SponsorVisibility') != 'members_only' )
            {
                $this->flash('error', "This user's sponsors are private.");
                return $this->redirectRoute('user_profile', ['username' => $user->getUsername()]);
            }
        }

        $viewerNetworks = [];
        // no need to do this if we're viewing our own profile
        if ( $currentUser && $currentUser->getId() != $user->getId() ) {
            foreach ( $currentUser->getNetworks() as $netmap ) {
                $viewerNetworks[] = $netmap->getNetwork()->getId();
            }
        }

        $sship40k = $this->repo('Scholarship')->getCurrent40K();
        $sponsors = [];
        $count = 0;
        $page = 1;
        $numPages = 1;

        if ( $sship40k ) {
            $entry40k = $user->getScholarshipEntry($sship40k);
            if ( $entry40k ) {
                $count = $this->repo('ScholarshipEntry')->countSponsors($entry40k);
                $numPages = ceil($count / $maxPerPage);
                $page = $request->query->getDigits('page', 1);
                $page = max(1, min($numPages, $page));
                $offset = ($page - 1) * $maxPerPage;
                $sponsors = $this->repo('ScholarshipEntry')->getSponsors($entry40k, $offset, $maxPerPage);
            }
        }

        $users = array_map(function(EntrySponsor $es) { return $es->getUser(); }, $sponsors);
        $this->repo('User')->precachePropertiesMulti($users, ['FirstName', 'LastName', 'PhotoURL']);

        return [
            'user' => $user,
            'sponsors' => $sponsors,
            'sponsorCount' => $count,

            'page' => $page,
            'numPages' => $numPages,
            'minPage' => max(1, $page - 2),
            'maxPage' => min($numPages, $page + 2),
        ];
    }

    /**
     * @param Request $request
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Method("POST")
     * @Route("/user-search-email", name="user_search_email")
     */
    public function searchEmailAction(Request $request)
    {
        /** @var User $entity */
        $entity = $this->repo('User')->findOneBy(['email' => $request->request->get('search')]);
        if ( $entity !== null && $entity->isEnabled() ) {
            return $this->redirectRoute('user_profile', ['username' => $entity->getUsername()]);
        }

        throw $this->createNotFoundException('The e-mail you searched does not exist in our database.');
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Method("GET")
     * @Route("/ajax-search-email", name="ajax_search_email")
     */
    public function ajaxSearchEmailAction(Request $request)
    {
        /** @var User $entity */
        $entity = $this->repo('User')->findOneBy(['email' => $request->query->get('search')]);
        if ( $entity !== null && $entity->isEnabled() ) {
            $profileUrl = $this->generateUrl('user_profile', ['username' => $entity->getUsername()]);
        } else {
            $profileUrl = '';
        }

        return $this->renderJson(['profileUrl' => $profileUrl]);
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/reset-username", name="reset_username")
     * @Template
     */
    public function updateUsernameAction(Request $request)
    {
        $builder = $this->createFormBuilder();
        $builder->add('username', 'text', [
            'label' => 'New Username',
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 4]),
                new Regex('/^[a-zA-Za0-9_]+$/'),
                new ReservedWords()
            ],
            'attr' => [
                'title' => 'Username must be at least 4 characters long and can only contain letters, numbers, and underscores.'
            ],
            'error_type' => 'inline',
        ]);

        /** @var UserRepository $userRepo */
        $userRepo = $this->repo('User');

        /** @var UserPropertyHandler $handler */
        $handler = $this->get('gotchosen.user_property_handler');
        $userRepo->setPropertyHandler($handler);

        $propRef = $userRepo->getPropertyReference('BirthDay');
        $propRef->createFormElement($builder);

        $form = $builder->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            /** @var UserManager $manager */
            $manager = $this->get('fos_user.user_manager');
            $username = $form->get('username')->getData();

            $existing = $manager->findUserByUsername($username);
            if ( $existing ) {
                $this->flash('error', 'Sorry, this username already exists.');
            } else {
                /** @var User $user */
                $user = $this->getUser();

                $oldUsername = $user->getUsername();
                $oldUsernameCanonical = $user->getUsernameCanonical();

                $user->setUsername($username);
                $user->setStatus(User::STATUS_ACTIVE);
                $manager->updateUser($user);

                $userRepo->setProperty($user, 'BirthDay',
                    $handler->transformToData($propRef, $form->get('BirthDay')->getData()));

                $this->em()->flush();
                $this->flash('success', 'Thanks, your username has been updated');

                if ( strtolower($oldUsername) == $oldUsernameCanonical ) {
                    $this->flash('info', '<strong>IMPORTANT</strong>: Our website was recently moved to a new server platform right after the '
                                            . 'last GotScholarship $40K and Monthly Scholarships ended. We still have all of your '
                                            . 'historical data from the previous scholarships saved and eventually you will be able to '
                                            . 'see your previous sponsors from past GotScholarship participation. Nothing was lost.<br /><br />'
                                            . 'Please contact us using the "Contact Us" link at the bottom of the site if you have any questions. '
                                            . 'Thanks! ~ The GotChosen Team');
                }
                else {

                }

                return $this->redirectRoute('user_my_profile');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/check-username", name="check_username", options={"i18n" = false})
     */
    public function checkUsernameAction(Request $request)
    {
        $username = $request->query->get('value');

        /** @var UserManager $userMgr */
        $userMgr = $this->get('fos_user.user_manager');
        $exists = $userMgr->findUserByUsername($username);

        return $this->renderJson(['exists' => $exists !== null]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/check-email", name="check_email", options={"i18n" = false})
     */
    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('value');

        /** @var UserManager $userMgr */
        $userMgr = $this->get('fos_user.user_manager');
        $exists = $userMgr->findUserByEmail($email);

        return $this->renderJson(['exists' => $exists !== null]);
    }

    /**
     *
     * @Route("/unsubscribe/{type}/{email}", name="user_unsubscribe")
     * @Template
     */
    public function unsubscribeAction($type, $email)
    {
        $notifType = $this->repo('NotificationType')->find($type);
        if ( !$notifType ) {
            throw $this->createNotFoundException('Unknown notification type');
        }

        $email = Strings::base64DecodeUrl($email);

        /** @var UserManager $userMgr */
        $userMgr = $this->get('fos_user.user_manager');
        /** @var User $user */
        $user = $userMgr->findUserByEmail($email);
        if ( !$user ) {
            throw $this->createNotFoundException('Unknown user');
        }

        $sub = NotificationSub::make($user, $notifType);

        if ( $oldsub = $user->hasNotificationSub($sub) ) {
            $user->removeNotificationSub($oldsub);
            $this->em()->remove($oldsub);
            $this->em()->flush();
        }

        return ['newsletter' => $notifType];
    }

    /**
     * @param Request $request
     *
     * @param $tab
     * @return array
     *
     * @Secure(roles="ROLE_USER")
     * @Route("/edit-profile/{tab}", name="user_profile_edit", defaults={"tab" = "basic-information"})
     * @Template
     */
    public function editAction(Request $request, $tab = 'basic-information')
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UserRepository $userRepo */
        $userRepo = $this->repo('User');

        $slugToName = [];
        $groupProps = [];
        $propKeys = [];
        $fileInfos = [];

        /** @var ProfilePropertyGroup[] $groups */
        $groups = $userRepo->getPropertyGroups();
        foreach ( $groups as $group ) {
            $slugToName[$group->getSlug()] = $group->getGroupName();
            if ( $group->getSlug() === $tab ) {
                $groupProps = $group->getProperties();
            }
        }

        $userRepo->precacheProperties($user);

        if ( $tab === 'change-password' ) {
            $response = $this->handleChangePwForm($user, $request, $userRepo);
            if ( $response instanceof RedirectResponse ) {
                return $response;
            }
            list($form, $passwordLogs) = $response;
        } else if ( $tab === 'scholarships' ) {
            $response = $this->handleScholarshipForm($user, $request, $userRepo);
            if ( $response instanceof RedirectResponse ) {
                return $response;
            }
            list($scholarships, $sponsorCount, $hasVideo) = $response;
            $form = false;
        } else if ( $tab === 'notifications' ) {
            $response = $this->handleNotificationForm($user, $request, $userRepo);
            if ( $response instanceof RedirectResponse ) {
                return $response;
            }
            $form = $response;
        } else if ( $tab === 'delete-account' ) {
            $response = $this->handleDeleteForm($user, $request, $userRepo);
            if ( $response instanceof RedirectResponse ) {
                return $response;
            }
            $form = $response;
        } else {
            $response = $this->handlePropertiesForm($user, $tab, $groupProps, $request, $userRepo);
            if ( $response instanceof RedirectResponse ) {
                return $response;
            }
            list($form, $propKeys, $fileInfos) = $response;
        }
        //$logger = $this->get('logger');
        //$logger->info('hasVideo' . $hasVideo);
        $tabTemplate = 'GotChosenSiteBundle:Profile:tab_' . str_replace('-', '_', $tab) . '.html.twig';
        return [
            'user' => $user,
            'tab' => $tab,
            'propertyGroups' => $groups,
            'slugs' => $slugToName,
            'tabTemplate' => $tabTemplate,
            'form' => $form ? $form->createView() : false,
            'properties' => $propKeys,
            'fileInfos' => $fileInfos,
            'scholarships' => isset($scholarships) ? $scholarships : false,
            'sponsorCount' => isset($sponsorCount) ? $sponsorCount : false,
            'passwordLogs' => isset($passwordLogs) ? $passwordLogs : false,
            'hasVideo' => isset($hasVideo) ? $hasVideo : false,
        ];
    }

    /**
     * @param Request $request
     * @param $property
     * @param $tab
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Secure(roles="ROLE_USER")
     * @Route("/_profile/clear-property/{property}/{tab}", name="user_profile_clear_property")
     */
    public function clearPropertyAction(Request $request, $property, $tab)
    {
        $user = $this->getUser();
        /** @var UserRepository $userRepo */
        $userRepo = $this->repo('User');

        /** @var UserPropertyHandler $handler */
        $handler = $this->get('gotchosen.user_property_handler');
        $userRepo->setPropertyHandler($handler);

        $userProp = $userRepo->getProfileProperty($user, $property);
        if ( $userProp ) {
            $handler->cleanup($userProp);
        }
        $userRepo->setProperty($user, $property, '', null, true);

        if ( $tab == '_home' ) {
            return $this->redirectRoute('user_profile', ['username' => $user->getUsername()]);
        } else {
            return $this->redirectRoute('user_profile_edit', ['tab' => $tab]);
        }
    }

    protected function handlePropertiesForm(User $user, $tab, $groupProps, Request $request,
                                            UserRepository $userRepo)
    {
        $formData = [];
        $formBuilder = $this->createFormBuilder();

        if ( $tab === 'basic-information' ) {
            $formData['email'] = $user->getEmail();
            $formBuilder->add('email', 'email', [
                'label' => 'E-mail',
                'required' => true,
                'constraints' => [new NotBlank(), new Email()],
            ]);
        }

        $fileInfos = [];
        $propKeys = [];

        /** @var UserProfile[] $userProperties */
        $userProperties = [];
        foreach ( $userRepo->getProperties($user, true) as $userProp ) {
            $userProperties[$userProp->getProperty()->getName()] = $userProp;
        }

        $exclusions = ['PhotoURL'];

        /** @var UserPropertyHandler $transformer */
        $transformer = $this->get('gotchosen.user_property_handler');
        $userRepo->setPropertyHandler($transformer);

        /** @var ProfileProperty $propRef */
        foreach ( $groupProps as $propRef ) {
            $pname = $propRef->getName();
            if ( in_array($pname, $exclusions) ) {
                continue;
            }

            $propRef->createFormElement($formBuilder, true);
            $formData[$pname] = $transformer->transformToForm($propRef, $user->getPropertyValue($pname));
            $propKeys[] = $pname;

            if ( !$propRef->shouldHidePrivacyControls() ) {
                $formBuilder->add("privacy_$pname", 'choice', [
                    'choices' => [
                        UserProfile::VISIBLE_PUBLIC => 'Everyone',
                        UserProfile::VISIBLE_PRIVATE => 'Private',
                        UserProfile::VISIBLE_MEMBERS => 'Members Only',
                    ],
                ]);

                if ( isset($userProperties[$pname]) ) {
                    $formData["privacy_$pname"] = $userProperties[$pname]->getVisibility();

                    if ( $propRef->getFieldType() == ProfileProperty::TYPE_FILE ) {
                        $fileInfos[$pname] = $userProperties[$pname]->getPropertyValue();
                    }
                } else {
                    $formData["privacy_$pname"] = $propRef->getDefaultVisibility();

                    if ( $propRef->getFieldType() == ProfileProperty::TYPE_FILE ) {
                        $fileInfos[$pname] = '';
                    }
                }
            }
        }

        $formBuilder->setData($formData);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            $successExtra = '';

            if ( $form->has('email') ) {
                $oldEmail = $user->getEmail();
                $newEmail = $form->get('email')->getData();
                if ( $oldEmail != $newEmail ) {
                    $user->setEmail($form->get("email")->getData());

                    // having this instantly logs out the user, and kills any flash messages,
                    // which is not user friendly at all. will probably need to design a better
                    // system for this in the future, like tracking a "verified" property, and
                    // restricting access to some site features based on that, so login still works.
                    //$user->setEnabled(false);

                    if ( null === $user->getConfirmationToken() ) {
                        $tokenGenerator = $this->get('fos_user.util.token_generator');
                        $user->setConfirmationToken($tokenGenerator->generateToken());
                    }
                    $this->container->get('fos_user.user_manager')->updateUser($user);
                    $this->sendReconfirmationEmail($user);

                    $successExtra = ' <strong>Please check your e-mail to confirm your new e-mail address.</strong>';
                }
            }

            foreach ( $groupProps as $propRef ) {
                $pname = $propRef->getName();
                if ( in_array($pname, $exclusions) ) {
                    continue;
                }

                $userRepo->setProperty($user, $pname,
                    $transformer->transformToData($propRef, $form->get($pname)->getData()),
                    $form->has("privacy_$pname") ? $form->get("privacy_$pname")->getData() : null);
            }

            $this->em()->flush();

            $this->flash('success', 'Your profile has been updated.' . $successExtra);
            $this->get('session')->save(); // buggy symfony memcached sessions strike again.

            return $this->redirectRoute('user_profile_edit', ['tab' => $tab]);
        }

        return [$form, $propKeys, $fileInfos];
    }

    protected function sendReconfirmationEmail(User $user)
    {
        $params = ['token' => $user->getConfirmationToken()];
        $url = $this->generateUrl('profile_reconfirm', $params, true);
        $body = $this->renderView('GotChosenSiteBundle:Emails:email_changed.txt.twig', array(
            'user' => $user,
            'confirmationUrl' => $url,
        ));

        $msg = \Swift_Message::newInstance(
            'GotChosen: Email confirmation request for "' . $user->getEmail() . '"')
            ->setFrom("noreply@gotchosen.com", 'GotChosen - automated message, do not reply')
            ->setTo($user->getEmail())
            ->setBody($body, 'text/plain');
        $this->mailer()->send($msg);
    }

    /**
     * @Route("/_profile/reconfirm/{token}", name="profile_reconfirm")
     */
    public function reconfirmAction($token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $userManager->updateUser($user);
        return $this->redirectRoute('user_my_profile');
    }

    protected function handleChangePwForm(User $user, Request $request, UserRepository $userRepo)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            // do this first, to log the previous password hash
            $passLog = PasswordLog::make($user, $request->getClientIp());
            $this->em()->persist($passLog);
            $this->em()->flush();

            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->updateUser($user);

            $response = $this->redirectRoute('user_profile_edit', ['tab' => 'change-password']);

            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response));

            $this->get('session')->getFlashBag()->get('success'); // clear out existing success flash

            $this->flash('success', 'Your password has been updated.');
            return $response;
        }

        $passwordLogs = $this->repo('PasswordLog')->findBy(['user' => $user->getId()], ['dateChanged' => 'DESC']);

        return [$form, $passwordLogs];
    }

    // not really a form
    protected function handleScholarshipForm($user, $request, $userRepo)
    {
        $scholarships = $this->repo('Scholarship')->getCurrentScholarships();

        $sship40k = $this->repo('Scholarship')->getCurrent40K();
        $sshipVideo = $this->repo('Scholarship')->getCurrentVideo();
        //$sponsors = [];
        //$sponsoring = [];
        $sponsorCount = 0;

        if ( $sship40k ) {
            $entry40k = $user->getScholarshipEntry($sship40k);
            if ( $entry40k ) {
                //$sponsors = $this->repo('ScholarshipEntry')->getSponsors($entry40k);
                $sponsorCount = $this->repo('ScholarshipEntry')->countSponsors($entry40k);
            }

            //$sponsoring = $this->repo('ScholarshipEntry')->getSponsoring($user, $sship40k);
        }
        $hasVideo = false;
        if ( $sshipVideo ) {
               if ($user->hasApplied($sshipVideo))
               {
                    $video = $this->repo('Video')->findOneBy(['scholarship' => $sshipVideo->getId(), 'user' => $user->getId()]);
                        if ( $video )
                        {
                            $hasVideo = true;
                        }
                }
        }

        return [$scholarships, $sponsorCount, 'hasVideo' => $hasVideo];
    }

    protected function handleNotificationForm(User $user, Request $request, UserRepository $userRepo)
    {
        $notificationTypes = $this->repo('NotificationType')->findAll();
        $currentSubIds = $user->getNotificationSubs()->map(function(NotificationSub $sub) {
            return $sub->getNotificationType()->getId();
        })->toArray();

        $formBuilder = $this->createFormBuilder();
        $formData = [];

        $notificationTypesById = [];

        /** @var NotificationType $notificationType */
        foreach ( $notificationTypes as $notificationType ) {
            $nid = $notificationType->getId();
            $notificationTypesById[$nid] = $notificationType;

            // kill me
            $isEG = stripos($notificationType->getCommType()->getTypeName(), 'evolution games') !== false;

            $formBuilder->add('notification_' . $nid, 'checkbox', [
                'label' => ($isEG ? '[EG]' : '') . $notificationType->getName(),
                'required' => false,
                'widget_checkbox_label' => 'widget',
            ]);
            $formData['notification_' . $nid] = in_array($nid, $currentSubIds);
        }

        $formBuilder->setData($formData);
        $form = $formBuilder->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            foreach ( $formData as $key => $x ) {
                $id = (int) str_replace('notification_', '', $key);
                $enabled = $form->get($key)->getData();
                $sub = NotificationSub::make($user, $notificationTypesById[$id]);

                if ( $enabled ) {
                    if ( !$user->hasNotificationSub($sub) ) {
                        $user->addNotificationSub($sub);
                        $this->em()->persist($sub);
                    }
                } else {
                    if ( $oldsub = $user->hasNotificationSub($sub) ) {
                        $user->removeNotificationSub($oldsub);
                        $this->em()->remove($oldsub);
                    }
                }
            }

            $this->em()->flush();
            $this->flash('success', 'Your profile has been updated.');
            return $this->redirectRoute('user_profile_edit', ['tab' => 'notifications']);
        }

        return $form;
    }

    public function handleDeleteForm(User $user, Request $request, UserRepository $userRepo)
    {
        $builder = $this->createFormBuilder();
        $builder->add('current_password', 'password', array(
            'label' => 'form.current_password',
            'translation_domain' => 'FOSUserBundle',
            'mapped' => false,
            'constraints' => new UserPassword(),
        ));

        $form = $builder->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            $user->setEnabled(false);
            $user->setStatus(User::STATUS_DISABLED_USER);

            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->updateUser($user);

            $body = $this->renderView('GotChosenSiteBundle:Emails:disabled_user.txt.twig', array(
               'user' => $user
            ));

            $msg = \Swift_Message::newInstance()
                ->setSubject('GotChosen: User account for e-mail "' . $user->getEmail() . '" has been cancelled')
                ->setFrom('noreply@gotchosen.com', 'GotChosen')
                ->setTo($user->getEmail())
                ->setBody($body);
            $this->mailer()->send($msg);

            $this->get('security.context')->setToken(null);
            $this->get('request')->getSession()->invalidate();

            $response = $this->redirectRoute('fos_user_security_login');

            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(GCSiteEvents::USER_ACCOUNT_DISABLED,
                new FilterUserResponseEvent($user, $request, $response));

            $this->flash('info', 'Your account has been deleted.');
            return $response;
        }

        return $form;
    }
}
