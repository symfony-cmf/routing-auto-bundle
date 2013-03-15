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
        $this->route1 = new \stdClass;
        $this->route2 = new \stdClass;
        $this->route3 = new \stdClass;
        $this->object = new \stdClass;
    }

    public function testPathStuff()
    {
        $this->builderContext->addPath('foobar1');
        $this->builderContext->addPath('foobar2');
        $res = $this->builderContext->getPathStack();
        $this->assertEquals(array('foobar1', 'foobar2'), $res);

        $res = $this->builderContext->getLastPath();
        $this->assertEquals('foobar2', $res);

        $this->builderContext->replaceLastPath('foobar3');
        $res = $this->builderContext->getPathStack();
        $this->assertEquals(array('foobar1', 'foobar3'), $res);

        $res = $this->builderContext->getPath();
        $this->assertEquals('foobar1/foobar3', $res);
    }

    public function testRouteStuff()
    {
        $this->builderContext->addRoute($this->route1);
        $this->builderContext->addRoute($this->route2);
        $res = $this->builderContext->getRouteStack();
        $this->assertEquals(array($this->route1, $this->route2), $res);

        $res = $this->builderContext->getLastRoute();
        $this->assertEquals($this->route2, $res);
    }

    public function testIsLastBuilder()
    {
        $this->builderContext->isLastBuilder(false);
        $res = $this->builderContext->isLastBuilder();
        $this->assertFalse($res);

        $this->builderContext->isLastBuilder(true);
        $res = $this->builderContext->isLastBuilder();
        $this->assertTrue($res);
    }

    public function testOtherStuff()
    {
        $this->builderContext->setObject($this->object);
        $this->assertSame($this->object, $this->builderContext->getObject());
    }
}
