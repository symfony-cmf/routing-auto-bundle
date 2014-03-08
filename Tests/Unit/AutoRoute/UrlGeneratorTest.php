<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\OperationStack;
use Prophecy\Prophet;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlGenerator;

class UrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $prophet;
    protected $driver;
    protected $serviceRegistry;
    protected $tokenProviders = array();

    public function setUp()
    {
        $this->prophet = new Prophet();

        $this->metadataFactory = $this->prophet->prophesize(
            'Metadata\MetadataFactoryInterface'
        );
        $this->metadata = $this->prophet->prophesize(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata'
        );
        $this->driver = $this->prophet->prophesize(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver\DriverInterface'
        );
        $this->serviceRegistry = $this->prophet->prophesize(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\ServiceRegistry'
        );
        $this->tokenProvider = $this->prophet->prophesize(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProviderInterface'
        );

        $this->urlGenerator = new UrlGenerator(
            $this->metadataFactory->reveal(),
            $this->driver->reveal(),
            $this->serviceRegistry->reveal()
        );
    }

    public function provideGenerateUrl()
    {
        return array(
            array(
                '/this/is/{token_the_first}/a/url',
                '/this/is/foobar_value/a/url',
                array(
                    'token_the_first' => array(
                        'provider' => 'foobar_provider',
                        'value' => 'foobar_value',
                    ),
                ),
            ),
            array(
                '/{this}/{is}/{token_the_first}/a/url',
                '/that/was/foobar_value/a/url',
                array(
                    'token_the_first' => array(
                        'provider' => 'foobar_provider',
                        'value' => 'foobar_value',
                    ),
                    'this' => array(
                        'provider' => 'barfoo_provider',
                        'value' => 'that',
                    ),
                    'is' => array(
                        'provider' => 'dobar_provider',
                        'value' => 'was',
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideGenerateUrl
     */
    public function testGenerateUrl($urlSchema, $expectedUrl, $tokenProviderConfigs)
    {
        $document = new \stdClass;
        $this->driver->getRealClassName('stdClass')->shouldBeCalled()
            ->willReturn('ThisIsMyStandardClass');

        $this->metadataFactory->getMetadataForClass('ThisIsMyStandardClass')->shouldBeCalled()
            ->willReturn($this->metadata);

        $this->metadata->getTokenProviderConfigs()->shouldBeCalled()
            ->willReturn($tokenProviderConfigs);

        $this->metadata->getUrlSchema()->shouldBeCalled()
            ->willReturn($urlSchema);

        foreach ($tokenProviderConfigs as $tokenName => $tokenProviderConfig) {
            $providerName = $tokenProviderConfig['provider'];

            $this->tokenProviders[$providerName] = $this->prophet->prophesize(
                'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProviderInterface'
            );

            $this->serviceRegistry->getTokenProvider($tokenProviderConfig['provider'])
                ->shouldBeCalled()
                ->willReturn($this->tokenProviders[$providerName]);

            $this->tokenProviders[$providerName]->getValue($document, $tokenProviderConfig)
                ->willReturn($tokenProviderConfig['value']);
        }

        $res = $this->urlGenerator->generateUrl($document);

        $this->assertEquals($expectedUrl, $res);
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }
}
