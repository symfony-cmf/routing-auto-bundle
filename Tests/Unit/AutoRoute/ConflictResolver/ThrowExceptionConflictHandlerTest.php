<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\ConflictResolver;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\ConflictResolver\ThrowExceptionConflictResolver;

class ThrowExceptionConflictResolverTest extends BaseTestCase
{
    protected $adapter;

    public function setUp()
    {
        parent::setUp();
        $this->conflictResolver = new ThrowExceptionConflictResolver();
        $this->urlContext = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext');
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\ConflictResolver\Exception\ExistingUrlException
     * @expectedExceptionMessage There already exists an auto route for URL "/foobar"
     */
    public function testResolveConflict()
    {
        $this->urlContext->getUrl()->willReturn('/foobar');
        $this->conflictResolver->resolveConflict($this->urlContext->reveal());
    }
}

