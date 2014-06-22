<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler\LeaveRedirectDefunctRouteHandler;

class LeaveRedirectDefunctRouteHandlerTest extends BaseTestCase
{
    protected $adapter;
    protected $urlContextCollection;

    public function setUp()
    {
        parent::setUp();
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Adapter\AdapterInterface');
        $this->urlContextCollection = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UrlContextCollection');
        $this->route1 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->route2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->route3 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->route4 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');

        $this->subjectObject = new \stdClass;

        $this->handler = new LeaveRedirectDefunctRouteHandler(
            $this->adapter->reveal()
        );
    }

    public function testHandleDefunctRoutes()
    {
        $this->urlContextCollection->getSubjectObject()->willReturn($this->subjectObject);
        $this->adapter->getReferringAutoRoutes($this->subjectObject)->willReturn(array(
            $this->route1, $this->route2
        ));
        $this->urlContextCollection->containsAutoRoute($this->route1->reveal())->willReturn(true);
        $this->urlContextCollection->containsAutoRoute($this->route2->reveal())->willReturn(false);
        $this->urlContextCollection->containsAutoRoute($this->route3->reveal())->willReturn(true);

        $this->route2->getAutoRouteTag()->willReturn('fr');
        $this->urlContextCollection->getAutoRouteByTag('fr')->willReturn($this->route4);

        $this->adapter->migrateAutoRouteChildren($this->route2->reveal(), $this->route4->reveal())->shouldBeCalled();
        $this->adapter->removeAutoRoute($this->route2->reveal())->shouldBeCalled();
        $this->adapter->createRedirectRoute($this->route2->reveal(), $this->route4->reveal())->shouldBeCalled();

        $this->handler->handleDefunctRoutes($this->urlContextCollection->reveal());
    }
}

