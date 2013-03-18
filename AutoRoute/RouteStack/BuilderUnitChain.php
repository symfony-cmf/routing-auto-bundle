<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;

/**
 * This class is responsible for aggregating route stack
 * builder units and executing them sequentially.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderUnitChain
{
    protected $chain;
    protected $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function addBuilderUnit($name, BuilderUnitInterface $unit)
    {
        $this->chain[$name] = $unit;
    }

    public function executeChain(BuilderContext $context)
    {
        foreach ($this->chain as $name => $builderUnit) {
            $routeStack = new RouteStack($context);
            $this->builder->build($routeStack, $builderUnit);
        }
    }
}
