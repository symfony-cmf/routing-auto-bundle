<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathNotExists\CreatePath;

class CreatePathTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->routeMaker = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\RouteMakerInterface'
        );

        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );

        $this->createPath = new CreatePath($this->routeMaker);
    }

    public function testCreatePath()
    {
        $this->routeMaker->expects($this->once())
            ->method('makeRoutes')
            ->with($this->builderContext);
        $this->createPath->execute($this->builderContext);
    }
}
