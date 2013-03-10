<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->phpcrSession = $this->getMock('PHPCR\SessionInterface');
        $this->builder = new Builder($this->phpcrSession);
        $this->builderUnit = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderUnitInterface'
        );
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );
        $this->route1 = new \stdClass;
    }

    public function testNotExists()
    {
        $this->builderUnit->expects($this->once())
            ->method('pathAction')
            ->with($this->builderContext);
        $this->builderContext->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('/test/path'));

        $this->builderContext->expects($this->exactly(2))
            ->method('getLastRoute')
            ->will($this->returnValue($this->route1));

        $this->phpcrSession->expects($this->once())
            ->method('nodeExists')
            ->with('/test/path')
            ->will($this->returnValue(false));

        $this->builderUnit->expects($this->once())
            ->method('notExistsAction')
            ->with($this->builderContext);

        $this->builder->build($this->builderUnit, $this->builderContext);
    }

    public function testExists()
    {
        $this->builderUnit->expects($this->once())
            ->method('pathAction')
            ->with($this->builderContext);
        $this->builderContext->expects($this->exactly(1))
            ->method('getPath')
            ->will($this->returnValue('/test/path'));
        $this->builderUnit->expects($this->exactly(1))
            ->method('existsAction')
            ->with($this->builderContext);

        // first two node paths exist, third is OK
        $this->phpcrSession->expects($this->exactly(1))
            ->method('nodeExists')
            ->with('/test/path')
            ->will($this->returnCallback(function ($path) {
                static $count = 0;
                if ($count == 2) {
                    return false;
                }

                $count++;
                return true;
            }));

        $this->builder->build($this->builderUnit, $this->builderContext);
    }
}
