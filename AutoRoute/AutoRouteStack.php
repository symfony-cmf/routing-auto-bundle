<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute;

/**
 * Special sub class for AutoRoutes.
 *
 * This enables us to reuse the same strategies for the
 * creation of auto routes as for the creation of the content
 * path routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteStack extends RouteStack
{
    public function close()
    {
        if (count($this->routes) > 1) {
            throw new \RuntimeException('You can only add one route to the AutoRouteStack.');
        }

        parent::close();
    }

    public function addRoute($route)
    {
        parent::addRoute($route);
    }
}
