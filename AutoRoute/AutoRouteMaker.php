<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteMaker
{
    protected $builder;
    protected $builderUnit;

    public function __construct(Builder $builder, BuilderUnitInterface $builderUnit)
    {
        $this->builder = $builder;
        $this->builderUnit = $builderUnit;
    }

    public function createOrUpdateAutoRoute(AutoRouteStack $autoRouteStack)
    {
        $this->builder->build($autoRouteStack, $this->builderUnit);
    }
}
