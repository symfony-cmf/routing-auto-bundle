<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;

/**
 * @todo: Should this be renamed to RouteStack?
 *
 *        A route stack would have all of route components l
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderContext
{
    protected $routeStacks = array();
    protected $object;

    public function addRouteStack($routeStack)
    {
        if (!$routeStack->isClosed()) {
            throw new \RuntimeException('Cannot add closed route stack to context');
        }

        $this->routeStacks[] = $routeStack;
    }

    public function getRouteNodes()
    {
        $routes = array();
        foreach ($this->routeStacks as $routeStack) {
            $routes = array_merge($routes, $routeStack->getRouteNodes());
        }
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }
}
