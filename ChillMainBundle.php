<?php

namespace Chill\MainBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Chill\MainBundle\DependencyInjection\SearchableServicesCompilerPass;

class ChillMainBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SearchableServicesCompilerPass());
    }
}
