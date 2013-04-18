<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\DefaultMaker;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker\DefaultMaker;

class DefaultMakerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->autoRouteMaker = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface'
        );

        $this->routeMaker = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface'
        );

        $this->routeStack =  $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();
        $this->autoRouteStack =  $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->defaultMaker = new DefaultMaker(
            $this->autoRouteMaker,
            $this->routeMaker
        );
    }

    public function testMakeWithAutoRouteStack()
    {
        $this->autoRouteMaker->expects($this->once())
            ->method('make')
            ->with($this->autoRouteStack);
        $this->defaultMaker->make($this->autoRouteStack);
    }

    public function testMakeWithNormalRouteStack()
    {
        $this->routeMaker->expects($this->once())
            ->method('make')
            ->with($this->routeStack);
        $this->defaultMaker->make($this->routeStack);
    }
}
