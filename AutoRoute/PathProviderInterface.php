<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * Classes implementing this interface add path elements
 * to the RouteStack which are later resolved to routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface PathProviderInterface extends BuilderServiceInterface
{
    /**
     * Add path elements to the route stack
     *
     * @return string
     */
    public function providePath(RouteStack $routeStack, array $options);
}
