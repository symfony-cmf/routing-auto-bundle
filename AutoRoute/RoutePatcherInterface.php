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
 * Class implementing this interface add any missing routes
 * between the last existing path and the terminal route.
 *
 * e.g. given the following path :
 *
 *     /this/path/exists/this/one/doesnt/CONTENT
 *
 * If the path "/this/route/exists" exists in PHPCR then
 * this class has to add routes to the RouteStack for "this"",
 * "one" and "doesnt".
 *
 * The terminal route name, CONTENT, is the AutoRoute and is
 * handled seperately.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface RoutePatcherInterface
{
    public function patch(RouteStack $routeStack);
}
