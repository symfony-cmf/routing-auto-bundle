<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteMaker
{
    protected $patcher;

    public function __construct(RoutePatcherInterface $patcher)
    {
        $this->patcher = $patcher;
    }

    public function makeRoute(RouteStack $routeStack, BuilderContext $context)
    {
        foreach ($context->getRouteStacks() as $stack) {
            $this->patcher->patchStack($stack, $context);
        }
    }
}
