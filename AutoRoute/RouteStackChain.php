<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Builder;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderUnitChain
{
    protected $builderUnitChain;
    protected $builder;

    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    public function addBuilderUnit($name, BuilderUnitInterface $builder)
    {
        $this->builderUnitChain[$name] = $builder;
    }

    public function executeChain(BuilderContext $context)
    {
        $i = 1;

        foreach ($this->builderUnitChain as $name => $builderUnit) {
            $routeStack = new RouteStack;
            $this->builder->build($builderUnit, $context);
        }
    }
}
