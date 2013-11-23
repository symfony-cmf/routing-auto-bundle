<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Strategy\AutoRouteChanged;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\AutoRouteChangedInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;

class NoOp implements AutoRouteChangedInterface
{
    protected $context;

    public function execute(BuilderContext $context)
    {
    }
}

