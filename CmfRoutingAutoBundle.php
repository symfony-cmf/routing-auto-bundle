<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\AutoRoutePass;

class CmfRoutingAutoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AutoRoutePass());
    }
}
