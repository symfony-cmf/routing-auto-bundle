<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\TokenProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProvider\ContentLocaleProvider;

class ContentLocaleProviderTest extends BaseTestCase
{
    protected $slugifier;
    protected $article;
    protected $urlContext;

    public function setUp()
    {
        parent::setUp();

        $this->urlContext = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext');
        $this->provider = new ContentLocaleProvider($this->slugifier->reveal());
    }

    public function testGetValue()
    {
        $this->urlContext->getLocale()->willReturn('de');
        $res = $this->provider->provideValue($this->urlContext->reveal(), array());
        $this->assertEquals('de', $res);
    }
}

