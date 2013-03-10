<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderUnit;

class BuilderUnitTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pathProvider = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProviderInterface'
        );
        $this->pathExists = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathExistsInterface'
        );
        $this->pathNotExists = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathNotExistsInterface'
        );
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );
        $this->builderUnit = new BuilderUnit(
            $this->pathProvider,
            $this->pathExists,
            $this->pathNotExists
        );
    }

    public function testPathAction()
    {
        $this->pathProvider->expects($this->once())
            ->method('providePath');
        $this->builderUnit->pathAction($this->builderContext);
    }

    public function testExistsAction()
    {
        $this->pathExists->expects($this->once())
            ->method('execute');
        $this->builderUnit->existsAction($this->builderContext);
    }

    public function testNotExistsAction()
    {
        $this->pathNotExists->expects($this->once())
            ->method('execute');
        $this->builderUnit->notExistsAction($this->builderContext);
    }
}

