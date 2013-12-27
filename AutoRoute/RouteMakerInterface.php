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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * Classes implementing this interface complete a route stack
 * by adding one PHPCR-ODM document for each path element contained
 * in the RouteStack.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 * @date 13/03/24
 */
interface RouteMakerInterface
{
    public function make(RouteStack $routeStack);
}
