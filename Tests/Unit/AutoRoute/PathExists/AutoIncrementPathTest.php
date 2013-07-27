<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists\AutoIncrementPath;

class AutoIncrementPathTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->routeMaker = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface'
        );

        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->builderContext = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext');

        $this->aiPath = new AutoIncrementPath($this->dm, $this->routeMaker);
        $this->route1 = $this->getMockBuilder('Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route')
            ->disableOriginalConstructor()
            ->getMock();
        $this->route2 = $this->getMockBuilder('Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route')
            ->disableOriginalConstructor()
            ->getMock();

        $this->content1 = new \stdClass;
        $this->content2 = new \stdClass;
    }

    public function provideAutoIncrement()
    {
        return array(
            array(false),
            array(true),
        );
    }

    /**
     * @dataProvider provideAutoIncrement
     */
    public function testAutoIncrement($testUpdate)
    {
        $this->routeStack->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('/foo/bar'));

        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));

        // if we test update we have the same content found
        // by the DM and set in the builder context
        $this->dm->expects($this->at(0))
            ->method('find')
            ->with(null, '/foo/bar')
            ->will($this->returnValue($this->route1));

        $this->route1->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->content1));

        if (true === $testUpdate) {
            $this->builderContext->expects($this->once())
                ->method('getContent')
                ->will($this->returnValue($this->content1));
        } else {
            $this->builderContext->expects($this->once())
                ->method('getContent')
                ->will($this->returnValue($this->content2));
        }

        if (false === $testUpdate) {
            $this->dm->expects($this->at(1))
                ->method('find')
                ->with(null, '/foo/bar-1')
                ->will($this->returnValue(new \stdClass));

            $this->dm->expects($this->at(2))
                ->method('find')
                ->with(null, '/foo/bar-2')
                ->will($this->returnValue(null));

            $this->routeStack->expects($this->once())
                ->method('replaceLastPathElement')
                ->with('bar-2');

            $this->routeMaker->expects($this->once())
                ->method('make')
                ->with($this->routeStack);
        }

        $this->aiPath->execute($this->routeStack);
    }

}
