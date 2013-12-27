<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;

class BuilderContextTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builderContext = new BuilderContext();
        $this->routeStack = $this->getMockBuilder('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack')->disableOriginalConstructor()->getMock();
        $this->object = new \stdClass;
    }

    public function testStageAndCommitRouteStack()
    {
        $this->routeStack->expects($this->once())
            ->method('isClosed')
            ->will($this->returnValue(true));

        $this->builderContext->stageRouteStack($this->routeStack);
        $this->builderContext->commitRouteStack();

        $this->assertCount(1, $this->builderContext->getRouteStacks());
    }

    public function testIgnoreEmptyPath()
    {
        $this->routeStack->expects($this->at(3))
            ->method('getPath')
            ->will($this->returnValue('route'));

        $this->routeStack->expects($this->at(4))
            ->method('getPath')
            ->will($this->returnValue(''));

        $this->routeStack->expects($this->at(5))
            ->method('getPath')
            ->will($this->returnValue('foo/bar'));

        $this->routeStack->expects($this->exactly(3))
            ->method('isClosed')
            ->will($this->returnValue(true));

        for ($i = 0; $i < 3; $i++) {
            $this->builderContext->stageRouteStack($this->routeStack);
            $this->builderContext->commitRouteStack();
        }

        $this->assertCount(3, $this->builderContext->getRouteStacks());
        $this->assertEquals('route/foo/bar', $this->builderContext->getFullPath());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testStageOpenRouteStack()
    {
        $this->routeStack->expects($this->once())
            ->method('isClosed')
            ->will($this->returnValue(false));

        $this->builderContext->stageRouteStack($this->routeStack);
        $this->builderContext->commitRouteStack();
    }

    public function testSetObject()
    {
        $this->builderContext->setContent($this->object);
        $this->assertSame($this->object, $this->builderContext->getContent());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCommitWithNoStagedRouteStack()
    {
        $this->builderContext->commitRouteStack();
    }

    public function testGetRoutes()
    {
        $this->routeStack->expects($this->once())
            ->method('getRoutes')
            ->will($this->returnValue(array(
                $r1 = new \stdClass,
                $r2 = new \stdClass,
            )));
        $this->builderContext->stageRouteStack($this->routeStack);
        $this->builderContext->commitRouteStack();
        $routes = $this->builderContext->getRoutes();
        $this->assertSame(array($r1, $r2), $routes);
    }

    public function testSetGetOriginalAutoRoutePath()
    {
        $this->builderContext->setOriginalAutoRoutePath('/this/is/path');
        $this->assertEquals('/this/is/path', $this->builderContext->getOriginalAutoRoutePath());
    }

    public function testExtraDocuemnts()
    {
        $d1 = new \stdClass;
        $d2 = new \stdClass;

        $this->builderContext->addExtraDocument($d1);
        $this->builderContext->addExtraDocument($d2);

        $this->assertSame(array($d1, $d2), $this->builderContext->getExtraDocuments());
    }
}
