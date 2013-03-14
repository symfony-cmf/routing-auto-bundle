<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface BuilderInterface
{
    public function build(BuilderUnitInterface $builderUnit, BuilderContext $context);
}

