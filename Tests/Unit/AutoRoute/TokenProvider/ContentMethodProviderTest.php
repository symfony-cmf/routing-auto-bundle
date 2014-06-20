<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\TokenProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProvider\ContentMethodProvider;

class ContentMethodProviderTest extends BaseTestCase
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
        $this->provider = new ContentMethodProvider($this->slugifier->reveal());
    }

    public function provideGetValue()
    {
        return array(
            array(
                array(
                    'method' => 'getTitle',
                    'slugify' => true,
                ),
                true,
            ),
            array(
                array(
                    'method' => 'getTitle',
                    'slugify' => false,
                ),
                true,
            ),
            array(
                array(
                    'method' => 'getMethodNotExist',
                    'slugify' => false,
                ),
                false,
            ),
        );
    }

    /**
     * @dataProvider provideGetValue
     */
    public function testGetValue($options, $methodExists = false)
    {
        $method = $options['method'];
        $this->urlContext->getSubjectObject()->willReturn($this->article);

        if (!$methodExists) {
            $this->setExpectedException(
                'InvalidArgumentException', 'Method "' . $options['method'] . '" does not exist'
            );
        } else {
            $expectedResult = 'This is value';
            $this->article->$method()->willReturn($expectedResult);
        }

        if ($options['slugify']) {
            $expectedResult = 'this-is-value';
            $this->slugifier->slugify('This is value')->willReturn($expectedResult);
        }

        $res = $this->provider->provideValue($this->urlContext->reveal(), $options);

        $this->assertEquals($expectedResult, $res);
    }
}
