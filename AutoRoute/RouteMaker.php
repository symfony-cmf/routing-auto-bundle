<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * Class responsible for delegating the creation of
 * routes to the appropriate class.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 * @date 13/03/17
 */
class RouteMaker
{
    protected $arm;
    protected $patcher;

    public function __construct(AutoRouteMaker $arm, RoutePatcherInterface $patcher)
    {
        $this->arm = $arm;
        $this->patcher = $patcher;
    }

    public function makeRoutes(RouteStack $routeStack)
    {
        if ($routeStack instanceOf AutoRouteStack) {
            $this->arm->createOrUpdateAutoRoute($routeStack);
        } else {
            $this->patcher->patch($routeStack);
        }
    }
}
