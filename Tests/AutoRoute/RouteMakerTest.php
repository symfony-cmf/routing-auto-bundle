<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker;

class RouteMakerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->arm = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteMaker'
        )->disableOriginalConstructor()->getMock();

        $this->routePatcher = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RoutePatcherInterface'
        );

        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->autoRouteStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->routeMaker = new RouteMaker($this->arm, $this->routePatcher);
    }

    public function testMakeRoutes()
    {
        $this->routePatcher->expects($this->once())
            ->method('patch')
            ->with($this->routeStack);
        $this->routeMaker->makeRoutes($this->routeStack);
    }

    public function testMakeRoutesWithAutoRouteStack()
    {
        $this->arm->expects($this->once())
            ->method('createOrUpdateAutoRoute')
            ->with($this->autoRouteStack);
        $this->routeMaker->makeRoutes($this->autoRouteStack);
    }
}
