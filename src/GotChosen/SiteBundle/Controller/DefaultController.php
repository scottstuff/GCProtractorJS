<?php

namespace GotChosen\SiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends BaseController
{
    /**
     * @Route("/hello/{name}/")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name, 'locale' => $this->get('session')->get('_locale'));
    }
}
