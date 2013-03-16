<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Builder;

/**
 * This class is responsible for aggregating route stack
 * builder units and executing them sequentially.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteStackBuilderUnitChain
{
    protected $chain;
    protected $builder;

    public function __construct(RouteStackBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function addRouteStackBuilderUnit($name, RouteStackBuilderUnitInterface $unit)
    {
        $this->chain[$name] = $unit;
    }

    public function executeChain(BuilderContext $context)
    {
        foreach ($this->chain as $name => $builderUnit) {
            $routeStack = new RouteStack;
            $this->builder->build($routeStack, $builderUnit, $context);
        }
    }
}
