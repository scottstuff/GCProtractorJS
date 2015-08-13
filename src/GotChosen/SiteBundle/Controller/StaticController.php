<?php

namespace GotChosen\SiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class StaticController
 * @package GotChosen\SiteBundle\Controller
 */
class StaticController extends BaseController
{
    /**
     * index
     *
     * @Route("/")
     * @Route("/index", name="index")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * loginDisabled
     *
     * @Route("/logindisabled", name="login-disabled")
     * @Template
     */
    public function loginDisabledAction()
    {
        return [];
    }

    /**
     * index
     *
     * @Route("/publisher", name="publisher")
     * @Route("/publisher", name="publisher_short", options={"i18n" = false})
     * @Route("/publishers", name="publishers")
     * @Route("/publishers", name="publishers_short", options={"i18n" = false})
     * @Template
     */
    public function publisherAction()
    {
        return [];
    }


    /**
     * Home.aspx
     *
     * @Route("/home", name="home")
     * @Template
     */
    public function homeAction()
    {
        return [];
    }

    /**
     * Scholarship.aspx?t={tab}
     *
     * @Route("/scholarship/{tab}", name="scholarship", defaults={"tab" = "about"})
     * @Template
     */
    public function scholarshipAction($tab)
    {
        $tab = strtolower($tab);

        if ( !in_array($tab, ['about', 'tips', 'how', 'rules', 'faq']) ) {
            $tab = 'about';
        }

        return ['tab' => $tab];
    }

    /**
     * EvolutionGames.aspx
     *
     * @Route("/evolution-games/{tab}", name="evolution_games", defaults={"tab" = "contest"})
     * @Template
     */
    /*public function evolutionGamesAction($tab)
    {
        $tab = strtolower($tab);
        if ( !in_array($tab, ['scholarship', 'contest', 'famewall']) ) {
            $tab = 'contest';
        }

        return ['tab' => $tab];
    }*/

    /**
     * @Route("/evolutiongames", name="evolution_games_short", options={"i18n" = false})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function evoGamesShortAction()
    {
        return $this->redirectRoute('eg_scholarship', ['tab' => 'contest']);
    }

    /**
     * MonthlyScholarship.aspx?t={tab}
     *
     * @Route("/monthly-scholarship/{tab}", name="monthly_scholarship", defaults={"tab" = "about"})
     * @Template
     */
    public function monthlyScholarshipAction($tab)
    {
        $tab = strtolower($tab);
        if ( !in_array($tab, ['about', 'rules', 'faq']) ) {
            $tab = 'about';
        }

        return ['tab' => $tab];
    }


    /**
     * @Route("/video-scholarship", name="video_scholarship_short", options={"i18n" = false})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function videoScholarshipShortAction()
    {
        return $this->redirectRoute('video_scholarship', ['tab' => 'about']);
    }
//    /**
//     * FinancialAid.aspx
//     *
//     * @Route("/financial-aid", name="financial_aid")
//     * @Template
//     */
//    public function financialAidAction()
//    {
//        return [];
//    }

    /**
     * AboutUs.aspx
     *
     * @Route("/about-us", name="about_us")
     * @Template
     */
    public function aboutUsAction()
    {
        return [];
    }

    /**
     * Terms.aspx
     *
     * @Route("/terms", name="terms")
     * @Template
     */
    public function termsAction()
    {
        return [];
    }

    /**
     * ContactUs.aspx
     *
     * @Route("/contact-us", name="contact_us")
     * @Template
     */
    public function contactUsAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('name', 'text', ['required' => true, 'constraints' => [new NotBlank()]])
            ->add('email', 'email',
                ['required' => true, 'label' => 'E-mail', 'constraints' => [new NotBlank(), new Email()]])
            ->add('phone', 'text', ['label' => 'Phone Number', 'required' => false])
            ->add('subject', 'text', ['required' => true, 'constraints' => [new NotBlank()]])
            ->add('message', 'textarea', ['required' => true, 'constraints' => [new NotBlank()]])
            ->getForm();

        $form->handleRequest($request);
        if ( $form->isValid() ) {
            $contact = $form->getData();

            $msg = \Swift_Message::newInstance('GotChosen Contact Request')
                ->setFrom($contact['email'])
                ->setTo('info@gotchosen.com')
                ->setBody($this->renderView('GotChosenSiteBundle:Emails:contact_in.txt.twig', $contact), 'text/plain');
            $this->mailer()->send($msg);

            $this->flash('success', 'Your contact request has been sent.');
            return $this->redirectRoute('contact_us');
        }

        return ['form' => $form->createView()];
    }

    /**
     *
     * @Route("/privacy-issue", name="privacy_issue")
     * @Template
     */
    public function privacyIssueAction()
    {
        return [];
    }
}
