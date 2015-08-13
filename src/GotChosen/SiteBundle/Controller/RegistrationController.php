<?php

namespace GotChosen\SiteBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use GotChosen\SiteBundle\Entity\ProfileProperty;
use GotChosen\SiteBundle\Entity\User;
use GotChosen\SiteBundle\Form\Type\RegistrationFormType;
use GotChosen\SiteBundle\Repository\UserRepository;
use GotChosen\User\UserPropertyHandler;
use GotChosen\Util\Strings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class RegistrationController
 * @package GotChosen\SiteBundle\Controller
 */
class RegistrationController extends BaseController
{
    /**
     * @Route("/register", name="fos_user_registration_register")
     */
    public function registerAction(Request $request)
    {
        $currentDate = new \DateTime;
        $cutoffDate = new \DateTime("2014-10-01 00:00:00");
        if ($currentDate >= $cutoffDate)
        {
            return $this->redirectRoute('login-disabled');
        }

        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');

        /** @var UserRepository $userRepo */
        $userRepo = $this->repo('User');

        /** @var UserPropertyHandler $handler */
        $handler = $this->get('gotchosen.user_property_handler');
        $userRepo->setPropertyHandler($handler);

        /** @var User $user */
        $user = $userManager->createUser();
        $user->setEnabled(true);

        $registrationProperties = [
            'FirstName', 'LastName', 'Gender', 'BirthDay'
        ];
        $propertyRefs = $userRepo->getPropertyReferences($registrationProperties);

        $form = $this->createForm(new RegistrationFormType($propertyRefs), $user);

        $form->handleRequest($request);

        if ( $form->isValid() && $userManager->findUserByUsername($form->get('username')->getData()) ) {
            $form->get('username')->addError(new FormError('Username already taken'));
        }

        if ( $form->isValid() && $userManager->findUserByEmail($form->get('email')->getData()) ) {
            $form->get('email')->addError(new FormError('E-mail address already taken'));
        }

        if ( $form->isValid() ) {
            // e-mail confirmation stuff
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setEnabled(false);
            $user->setStatus(User::STATUS_AWAITING_VERIFICATION);
            if ( null === $user->getConfirmationToken() ) {
                // Apparently the FOS Token Generator can and frequently will generate tokens
                // that are not unique. We have to make sure we're only working with tokens
                // that are not on any other user accounts.
                do {
                    $token = $tokenGenerator->generateToken();
                } while ( $userManager->findUserBy(['confirmationToken' => $token]) );

                $user->setConfirmationToken($token);
            }

            $customTarget = $request->request->get('_target');
            $this->sendConfirmationEmailMessage($user, $customTarget);

            $this->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
            $this->get('session')->save(); // workaround for issue with some session handlers, including memcached.

            $url = $this->get('router')->generate('fos_user_registration_check_email');

            // update general user fields
            $userManager->updateUser($user);

            // set notification subs
            $userRepo->installDefaultNotifications($user);

            /** @var $property ProfileProperty */
            foreach ( $propertyRefs as $propName => $property ) {
                $userRepo->setProperty($user, $propName,
                    $handler->transformToData($property, $form->get($propName)->getData()));
            }

            $this->em()->flush();

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->container->get('security.context')->setToken($token);

            /*
            if ( $customTarget ) {
                $response = $this->redirect($request->getUriForPath($customTarget));
            } else {
                $response = $this->redirectRoute('user_my_profile');
            }*/
            $response = $this->redirect($url);

            return $response;
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
            'properties' => $registrationProperties,
        ));
    }

    /**
     * @Route("/register/resend/{email}", name="register_resend")
     */
    public function resendConfirmationAction($email)
    {
        $email = Strings::base64DecodeUrl($email);
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);
        if ( $user === null ) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', htmlentities($email)));
        }

        $token = $user->getConfirmationToken();
        if ( empty($token) ) {
            $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());
            $this->get('fos_user.user_manager')->updateUser($user);
        }
        $this->sendConfirmationEmailMessage($user, null);

        $this->flash('success', 'The confirmation email was re-sent to ' . htmlentities($email));
        $this->get('session')->save();

        return $this->redirectRoute('fos_user_security_login');//, ['resent_confirmation' => 1]);
    }

    protected function sendConfirmationEmailMessage(User $user, $customTarget)
    {
        $params = ['token' => $user->getConfirmationToken()];
        if ( $customTarget ) {
            $params['_target'] = $customTarget;
        }
        $url = $this->generateUrl('fos_user_registration_confirm', $params, true);
        $rendered = $this->renderView('FOSUserBundle:Registration:email.txt.twig', array(
            'user' => $user,
            'confirmationUrl' =>  $url
        ));

        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($rendered));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('noreply@gotchosen.com', 'GotChosen - automated message, do not reply')
            ->setTo($user->getEmail())
            ->setBody($body);

        $this->get('mailer')->send($message);
    }

    /**
     * Tell the user to check his email provider
     *
     * @Route("/register/check-email", name="fos_user_registration_check_email")
     * @Method("GET")
     */
    public function checkEmailAction()
    {
        $email = $this->container->get('session')->get('fos_user_send_confirmation_email/email');
        $this->container->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->container->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:checkEmail.html.'.$this->getEngine(), array(
            'user' => $user,
        ));
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     *
     * @Route("/register/confirm/{token}", name="fos_user_registration_confirm")
     * @Method("GET")
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');
        $logger = $this->get('logger');
        $clientIp = $request->getClientIp();

        $logger->error("UserConfirmation [{$clientIp}]: Hit with token: {$token}");

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            $logger->error("UserConfirmation [{$clientIp}]: Did not find a user for the provided token.");
            $currentUser = $this->getUser();
            if ( $currentUser ) {
                $logger->error("UserConfirmation [{$clientIp}]: Found logged-in user and redirecting to profile: " . $currentUser->getUsername());
            }
            $route = $currentUser ? 'user_my_profile' : 'fos_user_security_login';
            return $this->redirectRoute($route);
        }

        $logger->error("UserConfirmation [{$clientIp}]: Found a user for the provided token: " . $user->getUsername());

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $user->setStatus(User::STATUS_ACTIVE);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            //$url = $this->container->get('router')->generate('fos_user_registration_confirmed');
            //$response = new RedirectResponse($url);
            if ( $customTarget = $request->query->get('_target') ) {
                $response = $this->redirect($request->getUriForPath($customTarget));
            } else {
                $response = $this->redirectRoute('user_my_profile');
            }
        }

        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }

    /**
     * Tell the user his account is now confirmed
     *
     * @Route("/register/confirmed", name="fos_user_registration_confirmed")
     * @Method("GET")
     */
    public function confirmedAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:confirmed.html.'.$this->getEngine(), array(
            'user' => $user,
        ));
    }

    protected function getEngine()
    {
        return $this->container->getParameter('fos_user.template.engine');
    }
}
