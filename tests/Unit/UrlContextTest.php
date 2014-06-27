<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Symfony\Cmf\Component\RoutingAuto\UrlContext;
use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;

class UrlContextTest extends BaseTestCase
{
    protected $urlContext;

    public function setUp()
    {
        parent::setUp();
        $this->subjectObject = new \stdClass;
        $this->autoRoute = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
    }

    public function testGetSet()
    {
        $urlContext = new UrlContext($this->subjectObject, 'fr');

        // locales
        $this->assertEquals('fr', $urlContext->getLocale());

        /// url
        $this->assertEquals(null, $urlContext->getUrl());
        $urlContext->setUrl('/foo/bar');
        $this->assertEquals('/foo/bar', $urlContext->getUrl());

        // subject object
        $this->assertEquals($this->subjectObject, $urlContext->getSubjectObject());

        // auto route
        $urlContext->setAutoRoute($this->autoRoute);
        $this->assertEquals($this->autoRoute, $urlContext->getAutoRoute());
    }
}

