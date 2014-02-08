<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathNotExists\CreatePath;

class CreatePathTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->routeMaker = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface'
        );

        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->createPath = new CreatePath($this->routeMaker);
    }

    public function testCreatePath()
    {
        $this->routeMaker->expects($this->once())
            ->method('make')
            ->with($this->routeStack);
        $this->createPath->execute($this->routeStack, array());
    }
}
