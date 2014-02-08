<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute;

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
        // note that we can't type hint here as this method is overridden.
        if (!$route instanceof AutoRoute) {
            throw new \BadMethodCallException('You must pass an AutoRoute document to addRoute');
        }

        parent::addRoute($route);
    }
}
