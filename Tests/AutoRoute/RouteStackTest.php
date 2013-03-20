<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

class RouteStackTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = new RouteStack($this->builderContext);
    }

    public function testAddPathElement()
    {
        $this->routeStack->addPathElements(array('foo', 'bar'));
        $this->assertEquals(array('foo', 'bar'), $this->routeStack->getPathElements());
        $this->routeStack->addPathElement('boz');
        $this->assertEquals(array('foo', 'bar', 'boz'), $this->routeStack->getPathElements());
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\CannotModifyClosedRouteStackException 
     */
    public function testAddPathElementToClosed()
    {
        $this->routeStack->close();
        $this->routeStack->addPathElement('asd');
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\InvalidPathElementException 
     */
    public function testAddEmptyPathElement()
    {
        $this->routeStack->addPathElement('');
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\InvalidPathElementException 
     */
    public function testAddPathElementWithPathSeparator()
    {
        $this->routeStack->addPathElement('this/is/wrong');
    }

    public function testReplaceLastPathElement()
    {
        $this->routeStack->addPathElements(array('foo', 'bar'));
        $this->routeStack->replaceLastPathElement('baz');
        $path = $this->routeStack->getPath();
        $this->assertEquals('foo/baz', $path);
    }
}
