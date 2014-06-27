<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Symfony\Cmf\Component\RoutingAuto\ServiceRegistry;
use Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface;

class ServiceRegistryTest extends \PHPUnit_Framework_TestCase
{
    private $serviceRegistry;
    private $tokenProvider;
    private $conflictResolver;
    private $defunctRouteHandler;

    public function setUp()
    {
        $this->serviceRegistry = new ServiceRegistry();
        $this->tokenProvider = $this->getMock('Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface');
        $this->conflictResolver = $this->getMock('Symfony\Cmf\Component\RoutingAuto\ConflictResolverInterface');
        $this->defunctRouteHandler = $this->getMock('Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface');
    }

    public function testRegistration()
    {
        $tps = array('tp_1', 'tp_2');
        $crs = array('cr_1', 'cr_2');
        $defunctRouteHandlers = array('dfrh_1', 'dfrh_2');

        foreach ($tps as $tp) {
            $this->serviceRegistry->registerTokenProvider($tp, $this->tokenProvider);
        }

        foreach ($crs as $cr) {
            $this->serviceRegistry->registerConflictResolver($cr, $this->conflictResolver);
        }

        foreach ($defunctRouteHandlers as $defunctRouteHandler) {
            $this->serviceRegistry->registerDefunctRouteHandler($defunctRouteHandler, $this->defunctRouteHandler);
        }

        foreach ($tps as $tp) {
            $res = $this->serviceRegistry->getTokenProvider($tp);
            $this->assertSame($this->tokenProvider, $res);
        }

        foreach ($crs as $cr) {
            $res = $this->serviceRegistry->getConflictResolver($cr);
            $this->assertSame($this->conflictResolver, $res);
        }

        foreach ($defunctRouteHandlers as $defunctRouteHandler) {
            $res = $this->serviceRegistry->getDefunctRouteHandler($defunctRouteHandler);
            $this->assertsame($this->defunctRouteHandler, $res);
        }
    }
}
