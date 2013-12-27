<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

class RouteStackTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = new RouteStack($this->builderContext);
        $this->route1 = new \stdClass;
        $this->route2 = new \stdClass;
    }

    public function testGetEmptyPath()
    {
        $this->assertEmpty($this->routeStack->getPaths());
        $this->assertEquals('', $this->routeStack->getFullPath());
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

    public function testGetFullPaths()
    {
        $this->builderContext->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue(''));
        $this->routeStack->addPathElements(array('bar', 'foo'));
        $fullPaths = $this->routeStack->getFullPaths();

        $this->assertCount(2, $fullPaths);
        $this->assertEquals(array(
            'bar',
            'bar/foo',
        ), $fullPaths);
    }
}
