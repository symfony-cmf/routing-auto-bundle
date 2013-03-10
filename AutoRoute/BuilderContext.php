<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderContext
{
    protected $pathStack = array();
    protected $routeStack = array();

    protected $isLastBuilder = false;

    public function addPath(string $part)
    {
        $this->pathStack[] = $part;
    }

    public function getLastPath()
    {
        return end($this->pathStack);
    }

    public function replaceLastPath($path)
    {
        array_pop($this->pathStack);
        $this->pathStack[] = $path;
    }

    public function getPathStack()
    {
        return $this->pathStack;
    }

    public function addRoute($route)
    {
        $this->routeStack[]= $route;
    }

    public function getLastRoute()
    {
        return end($this->routeStack);
    }

    public function getPath()
    {
        return implode('/', $this->pathStack);
    }

    public function isLastBuilder(boolean $isLastBuilder = null)
    {
        if (null === $isLastBuilder) {
            return $this->isLastBuilder;
        }

        $this->isLastBuilder = $isLastBuilder;
    }
}
