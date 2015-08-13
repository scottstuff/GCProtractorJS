<?php

namespace GotChosen\SiteBundle;

use GotChosen\SiteBundle\DependencyInjection\AddRequestToTemplateLoaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GotChosenSiteBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddRequestToTemplateLoaderPass());
    }
}
