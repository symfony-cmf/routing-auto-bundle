<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\RouteMaker;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker\AutoRouteMaker;
use Doctrine\Common\Collections\ArrayCollection;

class AutoRouteMakerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder(
            'Doctrine\ODM\PHPCR\DocumentManager'
        )->disableOriginalConstructor()->getMock();

        $this->uow = $this->getMockBuilder(
            'Doctrine\ODM\PHPCR\UnitOfWork'
        )->disableOriginalConstructor()->getMock();

        $this->metadata = $this->getMockBuilder(
            'Doctrine\ODM\PHPCR\Mapping\ClassMetadata'
        )->disableOriginalConstructor()->getMock();

        $this->phpcrSession = $this->getMock(
            'PHPCR\SessionInterface'
        );

        $this->arm = new AutoRouteMaker($this->dm);
        $this->doc = new \stdClass;

        $this->autoRoute1 = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute'
        );
        $this->autoRoute2 = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute'
        );
        $this->nonAutoRoute = new \stdClass;

        $this->autoRouteStack =  $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
    }

    protected function setupDocumentPersisted($isPersisted)
    {
        $this->dm->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));
        $this->dm->expects($this->once())
            ->method('getPhpcrSession')
            ->will($this->returnValue($this->phpcrSession));
        $this->phpcrSession->expects($this->once())
            ->method('nodeExists')
            ->will($this->returnValue($isPersisted));
    }

    public function testCreateOrUpdateAutoRouteForExisting()
    {
        $this->setupDocumentPersisted(true);

        $this->dm->expects($this->once())
            ->method('getReferrers')
            ->will($this->returnValue(new ArrayCollection(array(
                $this->autoRoute1,
                $this->nonAutoRoute
            ))));

        $this->autoRouteStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));

        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->doc));

        $this->autoRouteStack->expects($this->once())
            ->method('addRoute')
            ->with($this->autoRoute1);

        $this->arm->make($this->autoRouteStack);
    }

    public function testCreateOrUpdateAutoRouteForNew()
    {
        $this->setupDocumentPersisted(false);

        $testCase = $this;

        $this->autoRouteStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));

        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->doc));

        $this->autoRouteStack->expects($this->once())
            ->method('addRoute')
            ->will($this->returnCallback(function ($route) use ($testCase) {
                $testCase->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute', $route);
            }));

        $this->arm->make($this->autoRouteStack);
    }
}
