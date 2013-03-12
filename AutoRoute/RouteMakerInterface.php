<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface RouteMakerInterface
{
    public function makeRoutes(BuilderContext $context);
}
