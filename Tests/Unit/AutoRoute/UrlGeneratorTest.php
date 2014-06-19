<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlGenerator;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Prophecy\Argument;

class UrlGeneratorTest extends BaseTestCase
{
    protected $driver;
    protected $serviceRegistry;
    protected $tokenProviders = array();
    protected $urlContext;

    public function setUp()
    {
        parent::setUp();

        $this->metadataFactory = $this->prophesize('Metadata\MetadataFactoryInterface');
        $this->metadata = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata');
        $this->driver = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface');
        $this->serviceRegistry = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\ServiceRegistry');
        $this->tokenProvider = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProviderInterface');
        $this->urlContext = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext');

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
                        'name' => 'foobar_provider',
                        'value' => 'foobar_value',
                        'options' => array(),
                    ),
                ),
            ),
            array(
                '/{this}/{is}/{token_the_first}/a/url',
                '/that/was/foobar_value/a/url',
                array(
                    'token_the_first' => array(
                        'name' => 'foobar_provider',
                        'value' => 'foobar_value',
                        'options' => array(),
                    ),
                    'this' => array(
                        'name' => 'barfoo_provider',
                        'value' => 'that',
                        'options' => array(),
                    ),
                    'is' => array(
                        'name' => 'dobar_provider',
                        'value' => 'was',
                        'options' => array(),
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
        $this->urlContext->getSubjectObject()->willReturn($document);
        $this->driver->getRealClassName('stdClass')
            ->willReturn('ThisIsMyStandardClass');

        $this->metadataFactory->getMetadataForClass('ThisIsMyStandardClass')
            ->willReturn($this->metadata);

        $this->metadata->getTokenProviders()
            ->willReturn($tokenProviderConfigs);

        $this->metadata->getUrlSchema()
            ->willReturn($urlSchema);

        foreach ($tokenProviderConfigs as $tokenName => $tokenProviderConfig) {
            $providerName = $tokenProviderConfig['name'];

            $this->tokenProviders[$providerName] = $this->prophesize(
                'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProviderInterface'
            );

            $this->serviceRegistry->getTokenProvider($tokenProviderConfig['name'])
                ->willReturn($this->tokenProviders[$providerName]);

            $this->tokenProviders[$providerName]->provideValue($this->urlContext, $tokenProviderConfig['options'])
                ->willReturn($tokenProviderConfig['value']);
            $this->tokenProviders[$providerName]->configureOptions(Argument::type('Symfony\Component\OptionsResolver\OptionsResolverInterface'))->shouldBeCalled();
        }

        $res = $this->urlGenerator->generateUrl($this->urlContext->reveal());

        $this->assertEquals($expectedUrl, $res);
    }
}
