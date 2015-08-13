<?php

namespace GotChosen\SiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use Gaufrette\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;

class BaseController extends Controller
{
    private $_entityManager = null;

    /**
     * @return EntityManager
     */
    protected function em()
    {
        if ( $this->_entityManager === null ) {
            $this->_entityManager = $this->getDoctrine()->getManager();
        }
        return $this->_entityManager;
    }

    protected function repo($name)
    {
        if ( strpos($name, ':') === false ) {
            $name = "GotChosenSiteBundle:$name";
        }
        return $this->em()->getRepository($name);
    }

    protected function flash($key, $message)
    {
        return $this->get('session')->getFlashBag()->add($key, $message);
    }

    protected function redirectRoute($route, $params = array(), $refType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->redirect($this->generateUrl($route, $params, $refType));
    }

    protected function renderJson($data)
    {
        return new Response(json_encode($data));
    }

    /**
     * @return \Swift_Mailer
     */
    protected function mailer()
    {
        return $this->get('mailer');
    }

    /**
     * @param $key
     * @return Filesystem
     */
    protected function fs($key)
    {
        return $this->get($key . '_storage_filesystem');
    }

    protected function getEntityErrorMap($entity, AbstractType $formType = null)
    {
        /** @var ConstraintViolation[] $errors */
        $errors = $this->get('validator')->validate($entity);

        $mapping = [];
        foreach ( $errors as $err ) {
            if ( $formType === null ) {
                $mapping[$err->getPropertyPath()] = $err->getMessage();
            } else {
                $mapping[$formType->getName() . '_' . $err->getPropertyPath()] = $err->getMessage();
            }
        }

        return $mapping;
    }

    /**
     * @param $name
     * @param null $data
     * @param array $options
     * @return FormBuilderInterface
     */
    protected function createNamedFormBuilder($name, $data = null, array $options = [])
    {
        return $this->container->get('form.factory')->createNamedBuilder($name, 'form', $data, $options);
    }
}
