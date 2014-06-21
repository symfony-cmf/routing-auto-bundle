<?php

namespace Unit\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\DefunctRouteHandler\DelegatingDefunctRouteHandler;

class DelegatingDefunctRouteHandlerTest extends BaseTestCase
{
    protected $metadataFactory;
    protected $adapter;
    protected $serviceRegistry;
    protected $urlContextCollection;
    protected $metadata;

    public function setUp()
    {
        parent::setUp();
        $this->metadataFactory = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AutoRoute\Mapping\MetadataFactory');
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AutoRoute\Adapter\AdapterInterface');
        $this->serviceRegistry = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AutoRoute\ServiceRegistry');
        $this->urlContextCollection = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AutoRoute\UrlContextCollection');
        $this->metadata = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AutoRoute\Mapping\ClassMetadata');
        $this->delegatedHandler = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AutoRoute\DefunctRouteHandlerInterface');

        $this->subjectObject = new \stdClass;

        $this->delegatingDefunctRouteHandler = new DelegatingDefunctRouteHandler(
            $this->metadataFactory->reveal(),
            $this->adapter->reveal(),
            $this->serviceRegistry->reveal(),
            $this->urlContextCollection->reveal()
        );
    }

    public function testHandleDefunctRoutes()
    {
        $this->urlContextCollection->getSubjectObject()->willReturn($this->subjectObject);
        $this->adapter->getRealClassName('stdClass')->willReturn('stdClass');
        $this->metadataFactory->getMetadataForClass('stdClass')->willReturn($this->metadata);
        $this->metadata->getDefunctRouteHandler()->willReturn(array(
            'name' => 'foobar'
        ));
        $this->serviceRegistry->getDefunctRouteHandler('foobar')->willReturn($this->delegatedHandler);
        $this->delegatedHandler->handleDefunctRoutes($this->urlContextCollection->reveal())->shouldBeCalled();
        $this->delegatingDefunctRouteHandler->handleDefunctRoutes($this->urlContextCollection->reveal());
    }
}
