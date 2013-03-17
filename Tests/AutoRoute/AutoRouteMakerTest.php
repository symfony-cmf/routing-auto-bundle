<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteMaker;

class AutoRouteMakerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder'
        )->disableOriginalConstructor()->getMock();

        $this->builderUnit = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitInterface'
        );

        $this->autoRouteStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->autoRouteMaker = new AutoRouteMaker($this->builder, $this->builderUnit);
    }

    public function testCreateOrUpdateAutoRoute()
    {
        $testCase = $this;

        $this->builder->expects($this->once())
            ->method('build')
            ->will($this->returnCallback(function ($stack, $builderUnit) use ($testCase) {
                $testCase->assertInstanceOf(
                    'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack', 
                    $stack
                );
            }));

        $stack = $this->autoRouteMaker->createOrUpdateAutoRoute($this->autoRouteStack);
    }
}
