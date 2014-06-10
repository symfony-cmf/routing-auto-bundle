<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\TokenProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProvider\ContentDateTimeProvider;

class ContentDateTimeProviderTest extends BaseTestCase
{
    protected $slugifier;
    protected $article;
    protected $urlContext;

    public function setUp()
    {
        parent::setUp();

        $this->slugifier = $this->prophesize('Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface');
        $this->article = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article');
        $this->urlContext = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext');
        $this->provider = new ContentDateTimeProvider($this->slugifier->reveal());
    }

    public function provideGetValue()
    {
        return array(
            array(
                array(
                    'date_format' => 'Y-m-d',
                ),
                '2014-10-09'
            ),
            array(
                array(
                    'date_format' => 'Y/m/d',
                ),
                '2014/10/09'
            ),
        );
    }

    /**
     * @dataProvider provideGetValue
     */
    public function testGetValue($options, $expectedResult)
    {
        $options = array_merge(array(
            'method' => 'getDate',
            'slugify' => true,
        ), $options);

        $this->urlContext->getSubjectObject()->willReturn($this->article);
        $this->article->getDate()->willReturn(new \DateTime('2014-10-09'));

        $res = $this->provider->provideValue($this->urlContext->reveal(), $options);

        $this->assertEquals($expectedResult, $res);
    }
}
