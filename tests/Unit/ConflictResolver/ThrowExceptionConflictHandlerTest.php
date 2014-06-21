<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\AutoRoute\ConflictResolver;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\ConflictResolver\ThrowExceptionConflictResolver;

class ThrowExceptionConflictResolverTest extends BaseTestCase
{
    protected $adapter;

    public function setUp()
    {
        parent::setUp();
        $this->conflictResolver = new ThrowExceptionConflictResolver();
        $this->urlContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AutoRoute\UrlContext');
    }

    /**
     * @expectedException Symfony\Cmf\Component\RoutingAuto\AutoRoute\ConflictResolver\Exception\ExistingUrlException
     * @expectedExceptionMessage There already exists an auto route for URL "/foobar"
     */
    public function testResolveConflict()
    {
        $this->urlContext->getUrl()->willReturn('/foobar');
        $this->conflictResolver->resolveConflict($this->urlContext->reveal());
    }
}

