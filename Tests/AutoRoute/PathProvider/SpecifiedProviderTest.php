<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathExists\PathProvider;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProvider\SpecifiedProvider;

class SpecifiedProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->provider = new SpecifiedProvider;
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );

    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception\MissingOptionException
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
        $this->builderContext->expects($this->once())
            ->method('addPath')
            ->with('foo/bar');
        $this->provider->providePath($this->builderContext);
    }
}
