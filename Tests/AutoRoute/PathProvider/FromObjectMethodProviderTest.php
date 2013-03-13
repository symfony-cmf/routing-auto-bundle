<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathExists\PathProvider;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProvider\FromObjectMethodProvider;

class FromObjectMethodProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->provider = new FromObjectMethodProvider;
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );
        $this->object = new FromObjectMethodTestClass;
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception\MissingOptionException
     */
    public function testProvidePath_noMethod()
    {
        $this->provider->init(array());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testProvideMethod_invalidMethod()
    {
        $this->builderContext->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($this->object));
        $this->provider->init(array('method' => 'invalidMethod'));
        $this->provider->providePath($this->builderContext);
    }

    public function testProvideMethod()
    {
        $this->builderContext->expects($this->once())
            ->method('addPath')
            ->with('slug');
        $this->builderContext->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue($this->object));

        $this->provider->init(array('method' => 'getSlug'));
        $this->provider->providePath($this->builderContext);
    }
}

class FromObjectMethodTestClass
{
    public function getSlug()
    {
        return 'slug';
    }
}
