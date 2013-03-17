<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists\PathProvider;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider\FromObjectMethodProvider;

class FromObjectMethodProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->provider = new FromObjectMethodProvider;
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();
        $this->object = new FromObjectMethodTestClass;
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException
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
        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->object));
        $this->provider->init(array('method' => 'invalidMethod'));
        $this->provider->providePath($this->routeStack);
    }

    public function testProvideMethod()
    {
        $this->routeStack->expects($this->once())
            ->method('addPathElements')
            ->with(array('this', 'is', 'path'));
        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->object));

        $this->provider->init(array('method' => 'getSlug'));
        $this->provider->providePath($this->routeStack);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testProvideMethodWithInvalidReturnValue()
    {
        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->object));

        $this->provider->init(array('method' => 'getBadSlug'));
        $this->provider->providePath($this->routeStack);
    }
}

class FromObjectMethodTestClass
{
    public function getSlug()
    {
        return array('this', 'is', 'path');
    }

    public function getBadSlug()
    {
        return 'this/is/a/path';
    }
}
