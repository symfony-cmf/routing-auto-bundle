<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderUnitChain;

class BuilderUnitChainTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderInterface'
        );

        $this->builderUnitChain = new BuilderUnitChain($this->builder);
        $this->builderUnit1 = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderUnitInterface'
        );
        $this->builderUnit2 = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderUnitInterface'
        );
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );
    }

    public function testExecute()
    {
        $this->builderContext->expects($this->at(0))
            ->method('isLastBuilder')
            ->with(false);
        $this->builderContext->expects($this->at(1))
            ->method('isLastBuilder')
            ->with(true);
        $this->builder->expects($this->at(0))
            ->method('build')
            ->with($this->builderUnit1, $this->builderContext);
        $this->builder->expects($this->at(1))
            ->method('build')
            ->with($this->builderUnit2, $this->builderContext);

        $this->builderUnitChain->addBuilderUnit('builder_1', $this->builderUnit1);
        $this->builderUnitChain->addBuilderUnit('builder_2', $this->builderUnit2);
        $this->builderUnitChain->executeChain($this->builderContext);
    }
}
