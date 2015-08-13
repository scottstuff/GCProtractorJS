<?php

namespace GotChosen\SiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class EGStaticController extends BaseController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     *
     * @Route("/evolution-games", name="eg_scholarship")
     * @Template
     */
    public function scholarshipAction(Request $request)
    {
        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/developers", name="eg_developers")
     * @Template
     */
    public function developersAction()
    {
        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/rules", name="eg_rules")
     * @Template
     */
    public function rulesAction()
    {
        return $this->redirectRoute('eg_scholarship');

        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/rules/developers", name="eg_rules_developers")
     * @template
     */
    public function rulesDevContestAction()
    {
        return $this->redirectRoute('eg_scholarship');

        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/rules/scholarship", name="eg_rules_scholarship")
     * @Template
     */
    public function rulesScholarshipAction()
    {
        return $this->redirectRoute('eg_scholarship');

        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/faq", name="eg_faq")
     * @Template
     */
    public function faqAction()
    {
        return $this->redirectRoute('eg_scholarship');

        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/faq/developers", name="eg_faq_developers")
     * @Template
     */
    public function faqDevContestAction()
    {
        return $this->redirectRoute('eg_scholarship');

        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/faq/scholarship", name="eg_faq_scholarship")
     * @Template
     */
    public function faqScholarshipAction()
    {
        return $this->redirectRoute('eg_scholarship');

        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/unityinfo", name="eg_unityinfo")
     * @Template
     */
    public function unityApiInfoAction()
    {
        return $this->redirectRoute('eg_scholarship');

        return [];
    }

    /**
     * @return array
     *
     * @Route("/evolution-games/flashinfo", name="eg_flashinfo")
     * @Template
     */
    public function flashApiInfoAction()
    {
        return $this->redirectRoute('eg_scholarship');

        return [];
    }
}
