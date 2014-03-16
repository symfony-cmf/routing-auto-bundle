<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\TokenProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\PhpcrOdmAdapter;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProvider\ContentMethodProvider;

class ContentMethodTest extends BaseTestCase
{
    protected $slugifier;
    protected $article;

    public function setUp()
    {
        parent::setUp();

        $this->slugifier = $this->prophet->prophesize('Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface');
        $this->article = $this->prophet->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article');
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

        $res = $this->provider->provideValue($this->article->reveal(), $options);

        $this->assertEquals($expectedResult, $res);
    }
}
