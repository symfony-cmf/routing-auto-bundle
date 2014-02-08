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
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class CreatePath extends AbstractPathAction
{
    protected $routeMaker;

    public function __construct(RouteMakerInterface $routeMaker)
    {
        $this->routeMaker = $routeMaker;
    }

    public function execute(RouteStack $routeStack, array $options)
    {
        $this->routeMaker->make($routeStack);
    }
}
