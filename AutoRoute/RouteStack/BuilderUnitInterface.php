<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

use PHPCR\SessionInterface as PhpcrSession;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * Represents a route stack builder /unit/.
 *
 * The unit is a collection of classes required to
 * build and close a RouteStack.
 *
 * By design the class implementing this would be the
 * RouteStack\Builder which would implement the methods
 * with configurable classes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface BuilderUnitInterface
{
    /**
     * Provide an ordered list of route names, e.g.
     *
     *   array('this', 'will', 'be', 'a', 'url');
     *
     * Would represent:
     *  
     *   /this/would/be/a/url
     *
     * @param RouteStack $routeStack
     *
     * @return array
     */
    public function pathAction(RouteStack $routeStack);

    /**
     * Perform an action if the route names given by pathAction
     * resolve the an existing document.
     *
     * The action must ensure that the number of route names
     * given by the pathAction add up to the number of routes
     * in the RouteStack.
     *
     * @param RouteStack $routeStack
     *
     * @return void - action operates on RouteStack.
     */
    public function existsAction(RouteStack $routeStack);

    /**
     * Perform an action if the route names given by pathAction
     * do not resolve the an existing document.
     *
     * The action must ensure that the number of route names
     * given by the pathAction add up to the number of routes
     * in the RouteStack.
     *
     * @param RouteStack $routeStack
     *
     * @return void - action operates on route stack.
     */
    public function notExistsAction(RouteStack $routeStack);
}
