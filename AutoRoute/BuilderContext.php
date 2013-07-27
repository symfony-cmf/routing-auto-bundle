<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * This class might better be called AutoRouteRequest.
 * It holds all the RouteStack objects and the content document.
 *
 * All data needed to create the auto route is contained in this
 * class and everything involved in the route building process has
 * access to this class.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderContext
{
    /** @var RouteStack[] */
    protected $routeStacks = array();
    /** @var RouteStack */
    protected $stagedRouteStack;
    protected $content;

    /**
     * Return an ordered array of all routes from
     * all stacks.
     *
     * @return array
     */
    public function getRoutes()
    {
        $routes = array();
        foreach ($this->routeStacks as $routeStack) {
            $routes = array_merge($routes, $routeStack->getRoutes());
        }

        return $routes;
    }

    /**
     * Stage a route stack. TBH this is probably not
     * required now. Could probably be replaced simply
     * with addRouteStack.
     */
    public function stageRouteStack(RouteStack $routeStack)
    {
        $this->stagedRouteStack = $routeStack;
    }

    /**
     * As with above. This can probably be replaced with something
     * simpler.
     */
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

    /**
     * Return all route stacks.
     *
     * @return array
     */
    public function getRouteStacks()
    {
        return $this->routeStacks;
    }

    /**
     * Return the "top" route (last added) in
     * the stack.
     *
     * @return object
     */
    public function getTopRoute()
    {
        $routes = $this->getRoutes();
        return end($routes);
    }

    /**
     * Returns the complete path as determined
     * by the route stacks.
     *
     * Note that this path is not absolute.
     *
     * @return string
     */
    public function getFullPath()
    {
        $paths = array();
        foreach ($this->routeStacks as $routeStack) {
            $paths[] = $routeStack->getPath();
        }

        $path = implode('/', $paths);

        return $path;

    }

    /**
     * Set the content object (e.g. a blog post)
     *
     * @param object
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Returns the content object (e.g. a blog post)
     *
     * @return object
     */
    public function getContent()
    {
        return $this->content;
    }
}
