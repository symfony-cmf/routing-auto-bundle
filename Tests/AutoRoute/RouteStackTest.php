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

    public function testMe()
    {
    }

    public function testReplaceLastPathElement()
    {
        $this->routeStack->addPathElements(array('foo', 'bar'));
        $this->routeStack->replaceLastPathElement('baz');
        $path = $this->routeStack->getPath();
        $this->assertEquals('foo/baz', $path);
    }
}
