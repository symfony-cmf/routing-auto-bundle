<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnit;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteMaker
{
    protected $builder;
    protected $builderUnit;

    public function __construct(Builder $builder, BuilderUnit $builderUnit)
    {
        $this->builder = $builder;
        $this->bulderUnit = $builderUnit;
    }

    public function createOrUpdateAutoRoute(BuilderContext $context)
    {
        $stack = new AutoRouteStack($context);
        $this->builder->build($stack, $this->builderUnit);
    }
}
