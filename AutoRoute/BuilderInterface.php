<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface BuilderInterface
{
    public function build(BuilderUnitInterface $builderUnit, BuilderContext $context);
}

