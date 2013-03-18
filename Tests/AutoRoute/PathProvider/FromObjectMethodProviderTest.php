<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists\PathProvider;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider\FromObjectMethodProvider;

class FromObjectMethodProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();
        $this->slugifier = $this->getMock(
            'Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface'
        );

        $this->provider = new FromObjectMethodProvider($this->slugifier);
        $this->object = new FromObjectMethodTestClass();
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

    public function setupTest($slugify = true)
    {
        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->object));

        if ($slugify) {
            $this->slugifier->expects($this->any())
                ->method('slugify')
                ->will($this->returnCallback(function ($el) { return $el; }));
        }
    }

    public function testProvideMethod()
    {
        $this->setupTest();
        $this->routeStack->expects($this->once())
            ->method('addPathElements')
            ->with(array('this', 'is', 'path'));

        $this->provider->init(array('method' => 'getSlug'));
        $this->provider->providePath($this->routeStack);
    }

    public function testProvideMethodNoSlugify()
    {
        $this->setupTest(false);
        $this->routeStack->expects($this->once())
            ->method('addPathElements')
            ->with(array('this', 'is', 'path'));

        $this->provider->init(array('method' => 'getSlug', 'slugify' => false));
        $this->provider->providePath($this->routeStack);
    }

    public function testProvideMethodWithString()
    {
        $this->setupTest();

        $this->provider->init(array('method' => 'getStringSlug'));
        $this->provider->providePath($this->routeStack);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testProvideMethodWithAbsolute()
    {
        $this->setupTest();

        $this->provider->init(array('method' => 'getAbsoluteSlug'));
        $this->provider->providePath($this->routeStack);
    }
}

class FromObjectMethodTestClass
{
    public function getSlug()
    {
        return array('this', 'is', 'path');
    }

    public function getStringSlug()
    {
        return 'this/is/a/path';
    }

    public function getAbsoluteSlug()
    {
        return '/this/is/absolute';
    }

}
