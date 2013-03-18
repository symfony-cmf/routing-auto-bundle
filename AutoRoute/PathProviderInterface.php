<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * Classes implementing this interface add path elements
 * to the RouteStack which are later resolved to routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface PathProviderInterface
{
    /**
     * Initialize with config options
     */
    public function init(array $options);

    /**
     * Add path elements to the route stack
     *
     * @return string
     */
    public function providePath(RouteStack $routeStack);
}
