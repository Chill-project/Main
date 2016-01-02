<?php

namespace Chill\MainBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Chill\MainBundle\DependencyInjection\SearchableServicesCompilerPass;
use Chill\MainBundle\DependencyInjection\ConfigConsistencyCompilerPass;
use Chill\MainBundle\DependencyInjection\TimelineCompilerClass;
use Chill\MainBundle\DependencyInjection\RoleProvidersCompilerPass;
use Chill\MainBundle\DependencyInjection\CompilerPass\ExportsCompilerPass;

class ChillMainBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SearchableServicesCompilerPass());
        $container->addCompilerPass(new ConfigConsistencyCompilerPass());
        $container->addCompilerPass(new TimelineCompilerClass());
        $container->addCompilerPass(new RoleProvidersCompilerPass());
        $container->addCompilerPass(new ExportsCompilerPass());
    }
}
