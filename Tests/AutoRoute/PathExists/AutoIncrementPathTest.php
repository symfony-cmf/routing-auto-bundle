<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists\AutoIncrementPath;

class AutoIncrementPathTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->routeMaker = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker'
        )->disableOriginalConstructor()->getMock();

        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->aiPath = new AutoIncrementPath($this->dm, $this->routeMaker);
    }

    public function testAutoIncrement()
    {
        $this->routeStack->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('/foo/bar'));

        $this->dm->expects($this->at(0))
            ->method('find')
            ->with(null, '/foo/bar-1')
            ->will($this->returnValue(new \stdClass));

        $this->dm->expects($this->at(1))
            ->method('find')
            ->with(null, '/foo/bar-2')
            ->will($this->returnValue(null));

        $this->routeStack->expects($this->once())
            ->method('replaceLastPathElement')
            ->with('bar-2');

        $this->routeMaker->expects($this->once())
            ->method('makeRoutes')
            ->with($this->routeStack);

        $this->aiPath->execute($this->routeStack);

    }

}
