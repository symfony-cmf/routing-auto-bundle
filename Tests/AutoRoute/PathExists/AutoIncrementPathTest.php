<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathExists\AutoIncrementPath;

class AutoIncrementPathTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );

        $this->aiPath = new AutoIncrementPath($this->dm);
    }

    public function testAutoIncrement()
    {
        $this->builderContext->expects($this->once())
            ->method('getLastPath')
            ->will($this->returnValue('foobar'));

        $this->dm->expects($this->at(0))
            ->method('find')
            ->with(null, 'foobar-1')
            ->will($this->returnValue(new \stdClass));

        $this->dm->expects($this->at(1))
            ->method('find')
            ->with(null, 'foobar-2')
            ->will($this->returnValue(null));

        $this->builderContext->expects($this->once())
            ->method('replaceLastPath')
            ->with('foobar-2');

        $this->aiPath->execute($this->builderContext);
    }

}


