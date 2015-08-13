<?php

namespace GotChosen\SiteBundle\Controller;

use FOS\UserBundle\Model\UserManager;
use GotChosen\SiteBundle\Entity\EntrySponsor;
use GotChosen\SiteBundle\Entity\NotificationSub;
use GotChosen\SiteBundle\Entity\NotificationType;
use GotChosen\SiteBundle\Entity\ProfileProperty;
use GotChosen\SiteBundle\Entity\ProfilePropertyGroup;
use GotChosen\SiteBundle\Entity\Scholarship;
use GotChosen\SiteBundle\Entity\ScholarshipEntry;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Entity\UserProfile;
use GotChosen\SiteBundle\Repository\NotificationTypeRepository;
use GotChosen\SiteBundle\Repository\UserRepository;
use GotChosen\User\UserPropertyHandler;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\NotBlank;

class ScholarshipController extends BaseController
{
    /**
     * @param User $user
     * @param ProfileProperty[] $requiredProps
     * @return bool
     */
    private function canBypassForm(User $user, array $requiredProps)
    {
        foreach ( $requiredProps as $key => $prop ) {
            // don't think we need to do anything with types (yet)
            // but we have the ProfileProperty in case an "empty" value is something else for some properties.
            //$type = $prop->getFieldType();

            $value = $user->getPropertyValue($key, '');
            if ( empty($value) ) {
                return false;
            }
        }

        return true;
    }

    private function doApply(User $user, Scholarship $scholarship)
    {
        // add the scholarship entry, enable relevant notifications
        // 40K: scholarship news, sponsor notifications
        // monthly: scholarship news
        // eg: eg scholarship notifications, eg news

        $entry = ScholarshipEntry::make($scholarship, $user);
        $this->em()->persist($entry);

        /** @var NotificationTypeRepository $ntrepo */
        $ntrepo = $this->repo('NotificationType');

        $scholarshipInfo = $ntrepo->findOneBy(['name' => 'Scholarship Information']);
        $sponsorNotifications = $ntrepo->findOneBy(['name' => 'Sponsor Notifications']);

        $type = $scholarship->getScholarshipType();
        if ( $type === Scholarship::TYPE_40K ) {
            if ( $scholarshipInfo ) {
                $this->maybeAddSubscription($user, $scholarshipInfo);
            }
            if ( $sponsorNotifications ) {
                $this->maybeAddSubscription($user, $sponsorNotifications);
            }
        } else if ( $type === Scholarship::TYPE_MONTHLY ) {
            if ( $scholarshipInfo ) {
                $this->maybeAddSubscription($user, $scholarshipInfo);
            }
        } else if ( $type === Scholarship::TYPE_EVOGAMES ) {
            // Give the user tokens equal to number of days into the contest x 5
            $days = $scholarship->getStartDate()->diff(new \DateTime('now'))->days + 1;
            $tokens = $days * 5;
            $user->setTokens($tokens);
        } else {

        }

        $this->em()->flush();
    }

    private function flashAndRedirect(Scholarship $scholarship)
    {
        $sname = $scholarship->getScholarshipName();
        if ( strtolower(substr($sname, -strlen(' scholarship'))) != ' scholarship' ) {
            $sname .= ' Scholarship';
        }
        
        if ($scholarship->isVideo())
        {
            $this->flash('success', 'You are almost there.  To complete your application, please use the form below to submit your video entry.');
            return $this->redirectRoute('vs_submit');
        
        }
        else
        {
            $this->flash('success', 'You have successfully applied for the ' . $sname);
        
        }

        
        return $this->redirectRoute('user_my_profile');
    }

    /**
     *
     * @Route("/scholarship/apply/{id}", name="scholarship_apply")
     * @Secure(roles="ROLE_USER")
     * @Template
     */
    public function applyAction(Request $request, $id)
    {
        /** @var Scholarship $scholarship */
        $scholarship = $this->repo('Scholarship')->find($id);
        if ( !$scholarship ) {
            throw new NotFoundHttpException('Page Not Found');
        }

        /** @var User $user */
        $user = $this->getUser();

        if ( $user->hasApplied($scholarship) ) {
            $this->flash('error', 'You have already applied for this scholarship');
            return $this->redirectRoute('user_my_profile');
        }

        /** @var UserRepository $userRepo */
        $userRepo = $this->repo('User');

        /** @var UserPropertyHandler $handler */
        $handler = $this->get('gotchosen.user_property_handler');
        $userRepo->setPropertyHandler($handler);

        $userRepo->precacheProperties($user);

        $educationProps = [];
        $contactProps = [];

        /** @var ProfilePropertyGroup[] $groups */
        $groups = $userRepo->getPropertyGroups();
        foreach ( $groups as $group ) {
            if ( strpos($group->getSlug(), 'education') !== false ) {
                $educationProps = $group->getProperties();
            } else if ( strpos($group->getSlug(), 'contact') !== false ) {
                $contactProps = $group->getProperties();
            }
        }

        $userProperties = [];
        foreach ( $userRepo->getProperties($user) as $userProp ) {
            $userProperties[$userProp->getProperty()->getName()] = $userProp;
        }

        // form:
        // checkbox to accept rules and regulations
        // property groups: contact information, education information. all fields force required.
        // upon submit, add ScholarshipEntry, redirect to user profile

        $formData = [];
        $builder = $this->createFormBuilder();

        $optional = ['Address2', 'Telephone'];

        $contactPropKeys = [];
        $educationPropKeys = [];
        $requiredProps = [];
        foreach ( ['contact' => $contactProps, 'education' => $educationProps] as $gname => $propGroup ) {
            /** @var ProfileProperty $propRef */
            foreach ( $propGroup as $propRef ) {
                $pname = $propRef->getName();
                if ( $gname == 'contact' ) {
                    $contactPropKeys[] = $pname;
                } else {
                    $educationPropKeys[] = $pname;
                }

                if ( !in_array($pname, $optional) ) {
                    $requiredProps[$pname] = $propRef;
                }

                $propRef->createFormElement($builder, true, ['required' => !in_array($pname, $optional)]);
                $formData[$pname] = $handler->transformToForm($propRef, $user->getPropertyValue($pname));
                $propKeys[] = $pname;

                // privacy data
                if ( !$propRef->shouldHidePrivacyControls() ) {
                    $builder->add("privacy_$pname", 'choice', [
                        'choices' => [
                            UserProfile::VISIBLE_PUBLIC => 'Everyone',
                            UserProfile::VISIBLE_PRIVATE => 'Private',
                            UserProfile::VISIBLE_MEMBERS => 'Members Only',
                        ],
                    ]);

                    if ( isset($userProperties[$pname]) ) {
                        $formData["privacy_$pname"] = $userProperties[$pname]->getVisibility();
                    } else {
                        $formData["privacy_$pname"] = $propRef->getDefaultVisibility();
                    }
                }
            }
        }

        // if we have all required fields entered already, just apply for the scholarship and return.
        if ( $this->canBypassForm($user, $requiredProps) ) {
            $this->doApply($user, $scholarship);
            return $this->flashAndRedirect($scholarship);
        }

        $builder->setData($formData);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ( $form->isValid() ) {
            // set the properties
            foreach ( [$contactProps, $educationProps] as $propGroup ) {
                /** @var ProfileProperty $propRef */
                foreach ( $propGroup as $propRef ) {
                    $pname = $propRef->getName();
                    $userRepo->setProperty($user, $pname,
                        $handler->transformToData($propRef, $form->get($pname)->getData()),
                        $form->has("privacy_$pname") ? $form->get("privacy_$pname")->getData() : null);
                }
            }

            $this->doApply($user, $scholarship);
            return $this->flashAndRedirect($scholarship);
        }

        return [
            'scholarship' => $scholarship,
            'form' => $form->createView(),
            'contactPropKeys' => $contactPropKeys,
            'educationPropKeys' => $educationPropKeys,
        ];
    }

    private function maybeAddSubscription(User $user, NotificationType $type)
    {
        $sub = NotificationSub::make($user, $type);
        if ( !$user->hasNotificationSub($sub) ) {
            $this->em()->persist($sub);
        }
    }

    /**
     * This will include controls for logging in/registering if they’re not a member,
     * confirming the sponsorship, signing up for their own chance at the scholarship, and
     * getting more information about the scholarship. The landing page will be wrapped in
     * the second layout based on the wireframe above. The wireframe below is just the page
     * components:
     *
     * [Insert Scholarship Landing Wireframe]
     *
     * A confirmation screen will be presented after the user agrees to sponsor :username.
     * This will just be simple “Thank you for sponsoring :username!” copy presented inside of layout 2.
     *
     * @param Request $request
     * @param $username
     *
     * @return array
     * @throws NotFoundHttpException
     *
     * @Route("/scholarship/sponsor/{username}", name="scholarship_sponsor")
     * @Template
     */
    public function sponsorAction(Request $request, $username)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        /** @var User $user */
        $user = $userManager->findUserByUsername($username);

        if ( !$user ) {
            throw new NotFoundHttpException('User not found');
        }

        if ( $currentUser && $user->getId() == $currentUser->getId() ) {
            throw new NotFoundHttpException('You cannot sponsor yourself.');
        }

        $sship40k = $this->repo('Scholarship')->getCurrent40K();
        if ( !$sship40k ) {
            throw new NotFoundHttpException('There is no $40K scholarship running at this time.');
        }

        $entry40k = $user->getScholarshipEntry($sship40k);
        if ( !$entry40k ) {
            throw new NotFoundHttpException('This user has not applied for the scholarship.');
        }

        if ( $currentUser ) {
            /** @var EntrySponsor[] $sponsors */
            $sponsors = $this->repo('ScholarshipEntry')->getSponsors($entry40k);
            foreach ( $sponsors as $sponsor ) {
                if ( $sponsor->getUser()->getId() == $currentUser->getId() ) {
                    throw new NotFoundHttpException('You have already sponsored this user.');
                }
            }

            $sponsoring = $this->repo('ScholarshipEntry')->getSponsoring($currentUser, $sship40k);
            if ( count($sponsoring) > 0 ) {
                throw new NotFoundHttpException('You may only sponsor one user for this scholarship.');
            }
        }

        $builder = $this->createFormBuilder();
        $builder->add('confirm', 'hidden', ['data' => 1]);
        $form = $builder->getForm();

        $form->handleRequest($request);

        if ( $form->isValid() ) {
            $newSponsor = new EntrySponsor();
            $newSponsor->setEntry($entry40k);
            $newSponsor->setUser($currentUser);

            $this->em()->persist($newSponsor);
            $this->em()->flush();

            $this->repo('User')->precacheProperties($user);
            $this->repo('User')->precacheProperties($currentUser);

            /**
             * Determine if we have permission to mail the sponsored user.
             */ 
            if ( $user->hasNotificationSubByTypeName("Sponsor Notifications") ) { 
                $msg = \Swift_Message::newInstance(
                    'You Got Sponsored: GotChosen Additional Entry Confirmation for ' . $user->getEmail())
                    ->setFrom("noreply@gotchosen.com", 'GotChosen - automated message, do not reply')
                    ->setTo($user->getEmail())
                    ->setBody($this->renderView('GotChosenSiteBundle:Emails:sponsor_notification.txt.twig',
                        ['user' => $user, 'sponsor' => $currentUser]), 'text/plain');
                $this->mailer()->send($msg);
            }

            return $this->redirectRoute('scholarship_sponsor_thanks', ['username' => $username]);
        }

        return [
            'loginUrl' => $this->generateUrl('fos_user_security_login',
                ['_target' => $request->getPathInfo()]),
            'registerUrl' => $this->generateUrl('fos_user_registration_register',
                ['_target' => $request->getPathInfo()]),
            'user' => $user,
            'form' => $form->createView(),
            'scholarship' => $sship40k,
        ];
    }

    /**
     * @param $username
     *
     * @return array
     *
     * @Route("/scholarship/sponsor-thanks/{username}", name="scholarship_sponsor_thanks")
     * @Secure(roles="ROLE_USER")
     * @Template
     */
    public function sponsorThanksAction($username)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');

        /** @var User $user */
        $user = $userManager->findUserByUsername($username);

        $this->repo('User')->precacheProperties($user);

        $sship40k = $this->repo('Scholarship')->getCurrent40K();
        return ['user' => $user, 'scholarship' => $sship40k];
    }
}
