<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\RouteStack;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\RouteStack;

class RouteStackTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->rs = new RouteStack;
    }

    public function testRouteStack()
    {
        $this->rs->addPathElement('foo');
        $this->rs->addPathElement('bar');
        $this->rs->addPathElements(array('foo', 'bar'));

        $this->rs->addRoute($r1 = new \stdClass);
        $this->rs->addRoute($r2 = new \stdClass);
        $this->rs->addRoute($r3 = new \stdClass);
        $this->rs->addRoute($r4 = new \stdClass);

        $this->assertFalse($this->rs->isClosed());

        $this->rs->close();

        $this->assertTrue($this->rs->isClosed());

        $res = $this->rs->getRoutes();
        $this->assertEquals(array($r1, $r2, $r3, $r4), $res);


        $res = $this->rs->getPathElements();
        $this->assertEquals(array('foo', 'bar', 'foo', 'bar'), $res);

        $res = $this->rs->getPaths();
        $expected = array(
            '/foo', '/foo/bar', '/foo/bar/foo', '/foo/bar/foo/bar'
        );
        $this->assertEquals($res, $expected);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetRoutesOnOpenRouteStack()
    {
        $this->rs->getRoutes();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAddPathElementOnClosedRouteStack()
    {
        $this->rs->close();
        $this->rs->addPathElement('foo');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCloseRouteStackWithBadRoutePathRatio()
    {
        $this->rs->addPathElement('foo');
        $this->rs->close();
    }
}
