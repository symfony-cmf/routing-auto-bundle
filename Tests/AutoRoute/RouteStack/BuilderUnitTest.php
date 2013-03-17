<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\RouteStack;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnit;

class BuilderUnitTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pathProvider = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface'
        );
        $this->pathExists = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface'
        );
        $this->pathNotExists = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface'
        );
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        );
        $this->builderUnit = new BuilderUnit(
            $this->pathProvider,
            $this->pathExists,
            $this->pathNotExists
        );
    }

    public function testPathAction()
    {
        $this->pathProvider->expects($this->once())
            ->method('providePath');
        $this->builderUnit->pathAction($this->routeStack, $this->builderContext);
    }

    public function testExistsAction()
    {
        $this->pathExists->expects($this->once())
            ->method('execute');
        $this->builderUnit->existsAction($this->routeStack, $this->builderContext);
    }

    public function testNotExistsAction()
    {
        $this->pathNotExists->expects($this->once())
            ->method('execute');
        $this->builderUnit->notExistsAction($this->routeStack, $this->builderContext);
    }
}

