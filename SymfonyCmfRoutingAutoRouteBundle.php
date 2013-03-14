<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\DependencyInjection\Compiler\AutoRoutePass;

class SymfonyCmfRoutingAutoRouteBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AutoRoutePass());
    }
}
