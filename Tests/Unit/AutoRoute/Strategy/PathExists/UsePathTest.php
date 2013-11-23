<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\Strategy\PathExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\PathExists\UsePath;

class UsePathTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->usePath = new UsePath($this->dm);
        $this->routeObject = new \stdClass;
    }

    public function testUse()
    {
        $this->routeStack->expects($this->once())
            ->method('getFullPaths')
            ->will($this->returnValue(array(
                'foobar'
            )));

        $this->routeStack->expects($this->once())
            ->method('addRoute')
            ->with($this->routeObject);

        $this->dm->expects($this->at(0))
            ->method('find')
            ->with(null, 'foobar')
            ->will($this->returnValue($this->routeObject));

        $this->usePath->execute($this->routeStack);
    }

}



