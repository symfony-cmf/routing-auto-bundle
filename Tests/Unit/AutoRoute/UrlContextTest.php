<?php

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;

class UrlContextTest extends BaseTestCase
{
    protected $urlContext;

    public function setUp()
    {
        parent::setUp();
        $this->subjectObject = new \stdClass;
        $this->autoRoute = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface');
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

