<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\TokenProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\PhpcrOdmAdapter;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProvider\ContentDateTimeProvider;

class ContentDateTimeTest extends BaseTestCase
{
    protected $slugifier;
    protected $article;

    public function setUp()
    {
        parent::setUp();

        $this->slugifier = $this->prophet->prophesize('Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface');
        $this->article = $this->prophet->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article');
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

        $this->article->getDate()->willReturn(new \DateTime('2014-10-09'));

        $res = $this->provider->provideValue($this->article->reveal(), $options);

        $this->assertEquals($expectedResult, $res);
    }
}
