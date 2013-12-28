<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\RouteStackActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\CouldNotFindRouteException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ThrowException implements RouteStackActionInterface
{
    public function init(array $options)
    {
    }

    public function execute(RouteStack $routeStack)
    {
        throw new CouldNotFindRouteException('/'.$routeStack->getFullPath());
    }
}

