<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteStack
{
    protected $pathElements;
    protected $routes = array();

    protected $closed = false;

    public function addPathElements(array $pathElements)
    {
        foreach ($pathElements as $pathElement) {
            $this->addPathElement($pathElement);
        }
    }

    public function addPathElement($pathElement)
    {
        if (true === $this->closed) {
            throw new \RuntimeException('Cannot add path elements to a closed route stack.');
        }

        $this->pathElements[] = $pathElement;
    }

    public function getPathElements()
    {
        return $this->pathElements;
    }

    public function getPaths()
    {
        $tmp = array();

        foreach ($this->pathElements as $pathElement) {
            $tmp[] = $pathElement;
            $paths[] = '/'.implode('/', $tmp);
        }

        return $paths;
    }

    public function getPath()
    {
        return implode('/', $this->pathElements);
    }

    public function addRoute($route)
    {
        if (true === $this->closed) {
            throw new \RuntimeException('Cannot add path elements to a closed route stack.');
        }

        $this->routes[] = $route;
    }

    public function close()
    {
        if (count($this->routes) != count($this->pathElements)) {
            throw new \RuntimeException(sprintf(
                'Attempting to close route stack but the number of path elements (%d) '.
                'does not match number of route elements (%d). Registered path elements: "%s"',
                count($this->pathElements),
                count($this->routes),
                implode(',', $this->pathElements)
            ));
        }

        $this->closed = true;
    }

    public function getRoutes()
    {
        if (false === $this->closed) {
            throw new \RuntimeException(
                'You must close the route stack before retrieving the route nodes.'
            );
        }

        return $this->routes;
    }

    public function isClosed()
    {
        return $this->closed;
    }
}
