<?php

namespace Unit\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandler\LeaveRedirectDefunctRouteHandler;

class LeaveRedirectDefunctRouteHandlerTest extends BaseTestCase
{
    protected $adapter;
    protected $urlContextCollection;

    public function setUp()
    {
        parent::setUp();
        $this->adapter = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface');
        $this->urlContextCollection = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextCollection');
        $this->route1 = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface');
        $this->route2 = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface');
        $this->route3 = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface');
        $this->route4 = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface');

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

