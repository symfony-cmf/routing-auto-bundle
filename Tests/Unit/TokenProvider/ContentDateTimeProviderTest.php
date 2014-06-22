<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\TokenProvider;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ContentDateTimeProvider;

class ContentDateTimeProviderTest extends BaseTestCase
{
    protected $slugifier;
    protected $article;
    protected $urlContext;

    public function setUp()
    {
        parent::setUp();

        $this->slugifier = $this->prophesize('Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface');
        $this->article = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Article');
        $this->urlContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UrlContext');
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
