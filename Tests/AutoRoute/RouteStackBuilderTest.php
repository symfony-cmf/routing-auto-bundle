<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStackBuilder;

class RouteStackBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->phpcrSession = $this->getMock('PHPCR\SessionInterface');
        $this->routeStackBuilder = new RouteStackBuilder($this->phpcrSession);
        $this->routeStackBuilderUnit = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStackBuilderUnitInterface'
        );
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        );
        $this->route1 = new \stdClass;
    }

    public function testNotExists()
    {
        $this->routeStackBuilderUnit->expects($this->once())
            ->method('pathAction')
            ->with($this->routeStack, $this->builderContext);
        $this->builderContext->expects($this->once())
            ->method('getStagedPath')
            ->will($this->returnValue('/test/path'));

        $this->phpcrSession->expects($this->once())
            ->method('nodeExists')
            ->with('/test/path')
            ->will($this->returnValue(false));

        $this->routeStackBuilderUnit->expects($this->once())
            ->method('notExistsAction')
            ->with($this->routeStack, $this->builderContext);
    
        $this->routeStack->expects($this->once())
            ->method('close');

        $this->routeStackBuilder->build($this->routeStack, $this->routeStackBuilderUnit, $this->builderContext);
    }

    public function testExists()
    {
        $this->routeStackBuilderUnit->expects($this->once())
            ->method('pathAction')
            ->with($this->routeStack, $this->builderContext);
        $this->builderContext->expects($this->exactly(1))
            ->method('getStagedPath')
            ->will($this->returnValue('/test/path'));
        $this->routeStackBuilderUnit->expects($this->exactly(1))
            ->method('existsAction')
            ->with($this->routeStack, $this->builderContext);
        $this->routeStack->expects($this->once())
            ->method('close');

        // first two node paths exist, third is OK
        $this->phpcrSession->expects($this->exactly(1))
            ->method('nodeExists')
            ->with('/test/path')
            ->will($this->returnCallback(function ($path) {
                static $count = 0;
                if ($count == 2) {
                    return false;
                }

                $count++;
                return true;
            }));

        $this->routeStackBuilder->build($this->routeStack, $this->routeStackBuilderUnit, $this->builderContext);
    }
}
