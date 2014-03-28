<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlGenerator;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;

class UrlGeneratorTest extends BaseTestCase
{
    protected $driver;
    protected $serviceRegistry;
    protected $tokenProviders = array();

    public function setUp()
    {
        parent::setUp();

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
        $this->driver->getRealClassName('stdClass')
            ->willReturn('ThisIsMyStandardClass');

        $this->metadataFactory->getMetadataForClass('ThisIsMyStandardClass')
            ->willReturn($this->metadata);

        $this->metadata->getTokenProviderConfigs()
            ->willReturn($tokenProviderConfigs);

        $this->metadata->getUrlSchema()
            ->willReturn($urlSchema);

        foreach ($tokenProviderConfigs as $tokenName => $tokenProviderConfig) {
            $providerName = $tokenProviderConfig['provider'];

            $this->tokenProviders[$providerName] = $this->prophet->prophesize(
                'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProviderInterface'
            );

            $this->serviceRegistry->getTokenProvider($tokenProviderConfig['provider'])
                ->willReturn($this->tokenProviders[$providerName]);

            $this->tokenProviders[$providerName]->getValue($document, $tokenProviderConfig)
                ->willReturn($tokenProviderConfig['value']);
        }

        $res = $this->urlGenerator->generateUrl($document);

        $this->assertEquals($expectedUrl, $res);
    }
}
