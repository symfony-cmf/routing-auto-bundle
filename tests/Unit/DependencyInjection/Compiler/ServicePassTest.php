<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\ServicePass;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ServicePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ServicePass());
    }

    public function testServiceRegistration()
    {
        $serviceRegistryDefinition = new Definition();
        $this->setDefinition('cmf_routing_auto.service_registry', $serviceRegistryDefinition);

        $tokenProviderDefinition = new Definition();
        $tokenProviderDefinition->addTag('cmf_routing_auto.token_provider', ['alias' => 'foobar']);
        $this->setDefinition('some_token_provider', $tokenProviderDefinition);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'cmf_routing_auto.service_registry',
            'registerTokenProvider',
            [
                'foobar',
                new Reference('some_token_provider'),
            ]
        );
    }
}
