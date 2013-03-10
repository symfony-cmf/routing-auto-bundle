<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathExists\UsePath;

class UsePathTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );

        $this->usePath = new UsePath($this->dm);
        $this->routeObject = new \stdClass;
    }

    public function testAutoIncrement()
    {
        $this->builderContext->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('foobar'));

        $this->builderContext->expects($this->once())
            ->method('addRoute')
            ->with($this->routeObject);

        $this->dm->expects($this->at(0))
            ->method('find')
            ->with(null, 'foobar')
            ->will($this->returnValue($this->routeObject));

        $this->usePath->execute($this->builderContext);
    }

}



