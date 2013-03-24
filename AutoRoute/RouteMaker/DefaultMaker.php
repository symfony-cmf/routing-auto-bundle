<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface;

/**
 * Default route maker class - automatically delegates to an
 * appropriate maker depending on the context.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteMaker implements RouteMakerInterface
{
    protected $autoRouteMaker;
    protected $patcher;

    public function __construct(RouteMakerInterface $autoRouteMaker, RouteMakerInterface $patcher)
    {
        $this->autoRouteMaker = $autoRouteMaker;
        $this->patcher = $patcher;
    }

    public function make(RouteStack $routeStack)
    {
        if ($routeStack instanceOf AutoRouteStack) {
            $this->autoRouteMaker->make($routeStack);
        } else {
            $this->patcher->make($routeStack);
        }
    }
}
