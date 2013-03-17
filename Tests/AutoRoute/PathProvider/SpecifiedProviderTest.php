<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists\PathProvider;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider\SpecifiedProvider;

class SpecifiedProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->provider = new SpecifiedProvider;
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException
     */
    public function testProvidePath_noPath()
    {
        $this->provider->init(array());
    }

    public function testProvidePath()
    {
        $this->provider->init(array(
            'path' => 'foo/bar'
        ));
        $this->routeStack->expects($this->once())
            ->method('addPathElements')
            ->with(array('foo', 'bar'));
        $this->provider->providePath($this->routeStack);
    }
}
