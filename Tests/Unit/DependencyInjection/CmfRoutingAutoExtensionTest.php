<?php

namespace Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\CmfRoutingAutoExtension;

class CmfRoutingAutoExtensionTest extends AbstractExtensionTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setParameter('kernel.bundles', array(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Bundle\TestBundle\TestBundle'
        ));
    }

    protected function getContainerExtensions()
    {
        return array(
            new CmfRoutingAutoExtension()
        );
    }

    protected function loadPhpcrOdm()
    {
        $this->load(array(
            'persistence' => array(
                'phpcr' => array(
                    'enabled' => true
                )
            )
        ));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage enable one of the persistence layers
     */
    public function testLoad()
    {
        $this->setParameter('kernel.bundles', array());
        $this->load();
    }

    public function testAutoMappingRegistration()
    {
        $this->loadPhpcrOdm();

        $resources = $this->container->getParameter('cmf_routing_auto.metadata.loader.resources');

        // both the YAML and the XML files in the TestBundle have been registered
        $this->assertCount(2, $resources);
    }
}
