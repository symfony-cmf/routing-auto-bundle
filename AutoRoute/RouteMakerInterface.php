<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

/**
 * @todo: Rename this to RoutePatcherInterface
 *
 * Class implementing this interface "patch" 
 * any missing comonents in/a/routes/path
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface RouteMakerInterface
{
    public function makeRoutes(BuilderContext $context);
}
