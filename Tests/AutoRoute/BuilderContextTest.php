<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;

class BuilderContextTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builderContext = new BuilderContext();
        $this->routeStack = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack');
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
}
