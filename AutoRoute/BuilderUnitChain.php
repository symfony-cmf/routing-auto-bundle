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

            if ($i++ == count($this->builderUnitChain)) {
                $context->isLastBuilder(true);
            } else {
                // This may seem redundant, but it helps the unit test...
                $context->isLastBuilder(false);
            }

            $this->builder->build($builderUnit, $context);
        }
    }
}
