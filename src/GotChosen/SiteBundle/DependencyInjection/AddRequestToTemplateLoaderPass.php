<?php

namespace GotChosen\SiteBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddRequestToTemplateLoaderPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $defn = $container->getDefinition('twig.loader.filesystem');
        $defn->addMethodCall('setContainer', [new Reference('service_container')]);
    }
}