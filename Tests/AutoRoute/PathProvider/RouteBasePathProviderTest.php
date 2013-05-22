<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists\PathProvider;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider\RouteBasePathProvider;

class RouteBasePathProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->provider = new RouteBasePathProvider('/cms/test');
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        )->disableOriginalConstructor()->getMock();

        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->context));
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\BadProviderPositionException
     */
    public function testBadPosition()
    {
        // not exactly realistic but get the context to
        // return our self-same route stack.
        $this->context->expects($this->once())
            ->method('getRouteStacks')
            ->will($this->returnValue(array($this->routeStack)));

        $this->provider->providePath($this->routeStack);
    }

    public function testProvidePath()
    {
        $this->routeStack->expects($this->once())
            ->method('addPathElements')
            ->with(array('cms', 'test'));

        $this->provider->providePath($this->routeStack);
    }
}

