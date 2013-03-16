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

    public function testAddRouteStack()
    {
        $this->routeStack->expects($this->once())
            ->method('isClosed')
            ->will($this->returnValue(true));

        $this->builderContext->addRouteStack($this->routeStack);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAddOpenRouteStack()
    {
        $this->routeStack->expects($this->once())
            ->method('isClosed')
            ->will($this->returnValue(false));

        $this->builderContext->addRouteStack($this->routeStack);
    }

    public function testSetObject()
    {
        $this->builderContext->setObject($this->object);
        $this->assertSame($this->object, $this->builderContext->getObject());
    }
}
