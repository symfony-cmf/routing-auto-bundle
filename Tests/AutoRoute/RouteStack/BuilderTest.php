<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\RouteStack\RouteStack;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->phpcrSession = $this->getMock('PHPCR\SessionInterface');
        $this->routeStackBuilder = new Builder($this->phpcrSession);
        $this->routeStackBuilderUnit = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitInterface'
        );
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();
        $this->route1 = new \stdClass;
    }

    public function testNotExists()
    {
        $this->routeStackBuilderUnit->expects($this->once())
            ->method('pathAction')
            ->with($this->routeStack);
        $this->routeStack->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('/test/path'));

        $this->phpcrSession->expects($this->once())
            ->method('nodeExists')
            ->with('/test/path')
            ->will($this->returnValue(false));

        $this->routeStackBuilderUnit->expects($this->once())
            ->method('notExistsAction')
            ->with($this->routeStack);
    
        $this->routeStack->expects($this->once())
            ->method('close');

        $this->routeStackBuilder->build($this->routeStack, $this->routeStackBuilderUnit);
    }

    public function testExists()
    {
        $this->routeStackBuilderUnit->expects($this->once())
            ->method('pathAction')
            ->with($this->routeStack);

        $this->routeStack->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('/test/path'));

        $this->routeStackBuilderUnit->expects($this->exactly(1))
            ->method('existsAction')
            ->with($this->routeStack);

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

        $this->routeStackBuilder->build($this->routeStack, $this->routeStackBuilderUnit);
    }
}
