<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AbstractPathAction;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\CouldNotFindRouteException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ThrowException extends AbstractPathAction
{
    public function execute(RouteStack $routeStack, array $options)
    {
        throw new CouldNotFindRouteException('/'.$routeStack->getFullPath());
    }
}
