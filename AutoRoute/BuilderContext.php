<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderContext
{
    protected $routeStacks = array();
    protected $stagedRouteStack;
    protected $content;

    public function getRoutes()
    {
        $routes = array();
        foreach ($this->routeStacks as $routeStack) {
            $routes = array_merge($routes, $routeStack->getRoutes());
        }

        return $routes;
    }

    public function stageRouteStack(RouteStack $routeStack)
    {
        $this->stagedRouteStack = $routeStack;
    }

    public function commitRouteStack()
    {
        if (null === $this->stagedRouteStack) {
            throw new \RuntimeException(
                'Cannot commit route stack when there is no route stack to commit '.
                '(use stageRouteStack to stage)'
            );
        }

        if (false === $this->stagedRouteStack->isClosed()) {
            throw new \RuntimeException(
                'Staged route stack is not closed, cannot commit.'
            );
        }

        $this->routeStacks[] = $this->stagedRouteStack;
        $this->stagedRouteStack = null;
    }

    public function getRouteStacks()
    {
        return $this->routeStacks;
    }

    public function getTopRoute()
    {
        $routes = $this->getRoutes();
        return end($routes);
    }

    public function getFullPath()
    {
        $paths = array();
        foreach ($this->routeStacks as $routeStack) {
            $paths[] = $routeStack->getPath();
        }

        $path = implode('/', $paths);

        return $path;

    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}
