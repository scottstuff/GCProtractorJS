<?php

namespace GotChosen\SiteBundle\Localization;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;

class LocalizedTemplateLoader extends FilesystemLoader
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param string|TemplateReferenceInterface $template
     * @return string
     */
    protected function findTemplate($template)
    {
        $logicalName = (string) $template;
        if ( isset($this->cache[$logicalName]) ) {
            return $this->cache[$logicalName];
        }

        if ( $this->request === null && $this->container->isScopeActive('request') ) {
            $this->request = $this->container->get('request');
        }
        $locale = $this->request ? $this->request->attributes->get('_locale') : 'en';

        if ( is_string($template) ) {
            if ( strpos($template, ':') ) {
                try {
                    $template = $this->parser->parse($template);
                } catch ( \Exception $e ) {

                }
            } else {
                return parent::findTemplate($template);
            }
        }

        if ( $locale !== 'en' ) {
            $params = $template->all();
            $localizedTemplate = new TemplateReference($params['bundle'], $params['controller'], $params['name'],
                $params['format'], $params['engine']);

            if ( $params['controller'] ) {
                $localizedTemplate->set('controller', $locale . '/' . $params['controller']);
            } else {
                $localizedTemplate->set('name', $locale . '/' . $params['name']);
            }

            try {
                return parent::findTemplate($localizedTemplate);
            } catch ( \Twig_Error_Loader $e ) {
                return parent::findTemplate($template);
            }
        }

        return parent::findTemplate($template);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}