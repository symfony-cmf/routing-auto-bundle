<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;

/**
 * The job of classes implementing this interface is to add
 * a Route for each path element in the given RouteStack.
 *
 * The two scenarios are path "exists" and path "not_exists""
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface PathActionInterface
{
    /**
     * Initialize with config options
     *
     * @param array $options
     */
    public function init(array $options);

    /**
     * Perform the action. When the method has finished the
     * RouteStack should contain an equal number of routes and
     * path elements.
     *
     * @param RouteStack $stack
     */
    public function execute(RouteStack $stack);
}
