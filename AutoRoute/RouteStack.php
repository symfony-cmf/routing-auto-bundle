<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * This class is the context used when a builder unit
 * is being executed.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteStack
{
    protected $pathElements;
    protected $routes = array();
    protected $context;

    protected $closed = false;

    public function __construct(BuilderContext $context)
    {
        $context->stageRouteStack($this);
        $this->context = $context;
    }

    /**
     * Adds path elements, e.g.
     *
     *   array('this', 'is', 'a', 'path')
     *
     * @param array
     */
    public function addPathElements(array $pathElements)
    {
        foreach ($pathElements as $pathElement) {
            $this->addPathElement($pathElement);
        }
    }

    /**
     * Add a single path element
     *
     * @param string
     */
    public function addPathElement($pathElement)
    {
        if (!$pathElement) {
            throw new \RuntimeException('Empty path element passed to addPAthElement');
        }
        if (true === $this->closed) {
            throw new \RuntimeException('Cannot add path elements to a closed route stack.');
        }

        $this->pathElements[] = $pathElement;
    }

    /**
     * Return all path elements
     *
     * @return array
     */
    public function getPathElements()
    {
        return $this->pathElements;
    }

    /**
     * Return all the possible paths, e.g.
     *
     * Given path is: /this/is/a/path
     *
     * This method will return: 
     *
     *   - /this
     *   - /this/is
     *   - /this/is/a/
     *   - /this/is/a/path
     *
     * @return array
     */
    public function getPaths()
    {
        $tmp = array();

        foreach ($this->pathElements as $pathElement) {
            $tmp[] = $pathElement;
            $paths[] = implode('/', $tmp);
        }

        return $paths;
    }

    /**
     * Same as getPaths but prepends the current builder context
     * path. /almost/ giving you the abolute path (you need only
     * to add the "/" at the beginning)/
     *
     * @see getPaths
     *
     * @return array
     */
    public function getFullPaths()
    {
        $parentPath = $this->context->getFullPath($this);

        $paths = $this->getPaths();

        array_walk($paths, function (&$path) use ($parentPath) {
            $path = $parentPath.'/'.$path;
        });

        return $paths;
    }

    /**
     * Returns the full path, same as getFullPaths but
     * only returns the "top" path.
     *
     * @return string
     */
    public function getFullPath()
    {
        $parentPath = $this->context->getFullPath();

        $fullPath = $this->getPath();

        if ($parentPath) {
            $fullPath = $parentPath.'/'.$fullPath;
        }

        return $fullPath;
    }

    /**
     * Same as getFullPath but prepends the "/" to
     * make it absolute.
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return '/'.$this->getFullPath();
    }

    /**
     * Return the path given by joining all the
     * path elements.
     *
     * @return string
     */
    public function getPath()
    {
        return implode('/', $this->pathElements);
    }

    /**
     * Replace the last path element in the current path
     *
     * @param string $name
     */
    public function replaceLastPathElement($name)
    {
        array_pop($this->pathElements);
        $this->pathElements[] = $name;
    }

    /**
     * Add a route to the stack.
     *
     * Note you can only add routes to an open stack.
     *
     * @param object
     */
    public function addRoute($route)
    {
        if (true === $this->closed) {
            throw new \RuntimeException('Cannot add path elements to a closed route stack.');
        }

        $this->routes[] = $route;
    }

    /**
     * Close the stack. Closing a stack will check to see if
     * the number of path elements matches the number of routes and
     * prevents the addition of more routes. Also it enables the
     * getRoutes method.
     */
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

    /**
     * Return all routes, only works if RouteStack is closed.
     *
     * @return array
     */
    public function getRoutes()
    {
        if (false === $this->closed) {
            throw new \RuntimeException(
                'You must close the route stack before retrieving the route nodes.'
            );
        }

        return $this->routes;
    }

    /**
     * Return true if the route stack is closed
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Return the builder context associated with this
     * route stack
     */
    public function getContext()
    {
        return $this->context;
    }
}
