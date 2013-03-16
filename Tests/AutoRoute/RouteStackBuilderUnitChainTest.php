<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStackBuilderUnitChain;

class RouteStackBuilderUnitChainTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStackBuilder'
        )->disableOriginalConstructor()->getMock();

        $this->builderUnitChain = new RouteStackBuilderUnitChain($this->builder);
        $this->builderUnit1 = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStackBuilderUnitInterface'
        );
        $this->builderUnit2 = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStackBuilderUnitInterface'
        )->disableOriginalConstructor()->getMock();
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
    }

    public function testExecute()
    {
        // note that we cannot (or I do not know how to) test the "with" 
        // part because we instantiate the RouteStack in the class.
        $this->builder->expects($this->at(0))
            ->method('build');
            // ->with($this->builderUnit1, $this->builderContext);
        $this->builder->expects($this->at(1))
            ->method('build');
            // ->with($this->builderUnit2, $this->builderContext);

        $this->builderUnitChain->addRouteStackBuilderUnit('builder_1', $this->builderUnit1);
        $this->builderUnitChain->addRouteStackBuilderUnit('builder_2', $this->builderUnit2);
        $this->builderUnitChain->executeChain($this->builderContext);
    }
}
