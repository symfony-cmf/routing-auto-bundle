<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\Strategy\PathExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\PathExists\AutoIncrementPath;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\OnContentChange\LeaveRedirect;

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

        $this->route1 = $this->getMockBuilder('Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route')
            ->disableOriginalConstructor()
            ->getMock();
        $this->route2 = $this->getMockBuilder('Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route')
            ->disableOriginalConstructor()
            ->getMock();

        $this->content1 = new \stdClass;
        $this->content2 = new \stdClass;

        $this->leaveRedirect = new LeaveRedirect($this->dm);
    }

    public function testLeaveRedirect()
    {
        $me = $this;
        $parentRoute = $this->route1;

        $this->builderContext->expects($this->once())
            ->method('getOriginalAutoRoutePath')
            ->will($this->returnValue('/routes/original/path'));
        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/routes/original')
            ->will($this->returnValue($parentRoute));
        $this->builderContext->expects($this->once())
            ->method('getTopRoute')
            ->will($this->returnValue($this->route2));
        $this->builderContext->expects($this->once())
            ->method('addExtraDocument')
            ->will($this->returnCallback(function ($doc) use ($me, $parentRoute) {
                $me->assertEquals($doc->getName(), 'path');
                $me->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRedirectRoute', $doc);
                $me->assertSame($parentRoute, $doc->getParent());
            }));

        $this->leaveRedirect->execute($this->builderContext);
    }
}
