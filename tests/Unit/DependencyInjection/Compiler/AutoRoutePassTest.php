<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\AdapterPass;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
        $adapterDef->addTag('cmf_routing_auto.adapter', ['alias' => 'foobar']);
        $this->setDefinition('some_adapter', $adapterDef);
        $this->compile();
    }

    public function testAdapterRegistration()
    {
        $managerDef = new Definition();
        $managerDef->setPublic(true);
        $managerDef->setArguments([0, 1, 2]);
        $this->setDefinition('cmf_routing_auto.auto_route_manager', $managerDef);
        $this->container->setParameter('cmf_routing_auto.adapter_name', 'foobar');

        $adapterDef = new Definition();
        $adapterDef->addTag('cmf_routing_auto.adapter', ['alias' => 'foobar']);
        $this->setDefinition('some_adapter', $adapterDef);
        $this->compile();

        $expectedAlias = new Alias('some_adapter');
        $expectedAlias->setPublic(true);
        $this->assertEquals($expectedAlias, $this->container->getAlias('cmf_routing_auto.adapter'));
    }
}
