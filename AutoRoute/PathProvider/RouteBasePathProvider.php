<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\BadProviderPositionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * Provides the routing extra bundles route_basepath.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteBasePathProvider implements PathProviderInterface
{
    protected $routingBasePath;

    public function __construct($routeBasePath)
    {
        $this->routeBasePath = $routeBasePath;
    }

    public function init(array $options)
    {
    }

    public function providePath(RouteStack $routeStack)
    {
        $context = $routeStack->getContext();

        if (count($context->getRouteStacks()) > 0) {
            throw new BadProviderPositionException(
                'RouteBasePathProvider must belong to the first builder unit - adding '.
                'the full routing basepath at an intermediate point would be senseless.'
            );
        }

        $id = $this->routeBasePath;
        $id = substr($id, 1);
        $pathElements = explode('/', $id);
        $routeStack->addPathElements($pathElements);
    }
}
