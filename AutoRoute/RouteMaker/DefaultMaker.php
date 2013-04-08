<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteStack;

/**
 * Default route maker class - automatically delegates to an
 * appropriate maker depending on the context.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DefaultMaker implements RouteMakerInterface
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
