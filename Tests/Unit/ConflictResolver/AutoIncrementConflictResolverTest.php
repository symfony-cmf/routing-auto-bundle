<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\ConflictResolver;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\ConflictResolver\AutoIncrementConflictResolver;

class AutoIncrementConflictResolverTest extends BaseTestCase
{
    protected $adapter;

    public function setUp()
    {
        parent::setUp();

        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Adapter\AdapterInterface');

        $this->conflictResolver = new AutoIncrementConflictResolver($this->adapter->reveal());
        $this->urlContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UrlContext');
    }

    public function provideResolveConflict()
    {
        return array(
            array(
                '/foobar/bar',
                array(
                    '/foobar/bar-1',
                ),
                '/foobar/bar-2'
            ),
            array(
                '/foobar/bar',
                array(
                    '/foobar/bar-1',
                    '/foobar/bar-2',
                    '/foobar/bar-4',
                ),
                '/foobar/bar-3'
            )
        );
    }

    /**
     * @dataProvider provideResolveConflict
     */
    public function testResolveConflict($url, $existingRoutes, $expectedResult)
    {
        $this->urlContext->getUrl()->willReturn($url);

        foreach ($existingRoutes as $existingRoute) {
            $this->adapter->findRouteForUrl($existingRoute)->willReturn(new \stdClass);
        }
        $this->adapter->findRouteForUrl($expectedResult)->willReturn(null);

        $url = $this->conflictResolver->resolveConflict($this->urlContext->reveal());
        $this->assertEquals($expectedResult, $url);
    }
}
