<?php

use Symfony\Component\DependencyInjection\Definition;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\AdapterPass;

class AutoRoutePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AdapterPass());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not find configured adapter "bar", available adapters: "foobar"
     */
    public function testAdapterRegistrationUnknownAdapter()
    {
        $managerDef = new Definition();
        $this->setDefinition('cmf_routing_auto.auto_route_manager', $managerDef);
        $this->container->setParameter('cmf_routing_auto.adapter_name', 'bar');

        $adapterDef = new Definition();
        $adapterDef->addTag('cmf_routing_auto.adapter', array('alias' => 'foobar'));
        $this->setDefinition('some_adapter', $adapterDef);
        $this->compile();
    }

    public function testAdapterRegistration()
    {
        $managerDef = new Definition();
        $managerDef->setArguments(array(0, 1, 2));
        $this->setDefinition('cmf_routing_auto.auto_route_manager', $managerDef);
        $this->container->setParameter('cmf_routing_auto.adapter_name', 'foobar');

        $adapterDef = new Definition();
        $adapterDef->addTag('cmf_routing_auto.adapter', array('alias' => 'foobar'));
        $this->setDefinition('some_adapter', $adapterDef);
        $this->compile();

        $this->assertEquals(new Alias('some_adapter'), $this->container->getAlias('cmf_routing_auto.adapter'));
    }
}
