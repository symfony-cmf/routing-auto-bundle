<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\RouteStack\RouteStack;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitChain;

class BuilderUnitChainTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder'
        )->disableOriginalConstructor()->getMock();

        $this->builderUnitChain = new BuilderUnitChain($this->builder);
        $this->builderUnit1 = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitInterface'
        );
        $this->builderUnit2 = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitInterface'
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

        $this->builderUnitChain->addBuilderUnit('builder_1', $this->builderUnit1);
        $this->builderUnitChain->addBuilderUnit('builder_2', $this->builderUnit2);
        $this->builderUnitChain->executeChain($this->builderContext);
    }
}
