<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\CmfRoutingAutoExtension;

class CmfRoutingAutoExtensionTest extends AbstractExtensionTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setParameter('kernel.bundles', [
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\TestBundle',
        ]);
    }

    /**
     * An exception should be thrown if an adapter has not been explicitly or
     * implicitly configured.
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage No adapter has been configured, you either need to
     */
    public function testLoad()
    {
        $this->setParameter('kernel.bundles', []);
        $this->load();
    }

    /**
     * It should be possible to explicitly specify an adapter.
     */
    public function testExplicitAdapter()
    {
        $this->load([
            'adapter' => 'foobar',
        ]);

        $adapter = $this->container->getParameter('cmf_routing_auto.adapter_name');
        $this->assertEquals('foobar', $adapter);
    }

    /**
     * The adapter should be implicitly configured if the PHPCR ODM integration has
     * been enabled.
     */
    public function testImplicitPhpcrOdmAdapter()
    {
        $this->loadPhpcrOdm();

        $adapter = $this->container->getParameter('cmf_routing_auto.adapter_name');
        $this->assertEquals('doctrine_phpcr_odm', $adapter);
    }

    /**
     * It should be possible to override the implicitly configured adapter.
     */
    public function testOverrideImplicitPhpcrOdmAdapter()
    {
        $this->load([
            'adapter' => 'foobar',
            'persistence' => [
                'phpcr' => [
                    'enabled' => true,
                ],
            ],
        ]);
        $adapter = $this->container->getParameter('cmf_routing_auto.adapter_name');
        $this->assertEquals('foobar', $adapter);
    }

    /**
     * The bundle should automatically register routing auto mapping configuration in
     * the Resources/config directory.
     */
    public function testAutoMappingRegistration()
    {
        $this->loadPhpcrOdm();

        $resources = $this->container->getParameter('cmf_routing_auto.metadata.loader.resources');

        $this->assertCount(2, $resources);
    }

    protected function getContainerExtensions()
    {
        return [
            new CmfRoutingAutoExtension(),
        ];
    }

    protected function loadPhpcrOdm()
    {
        $this->load([
            'persistence' => [
                'phpcr' => [
                    'enabled' => true,
                ],
            ],
        ]);
    }
}
