<?php

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\DependencyInjection\Compiler;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\AutoRoutePass;
use Symfony\Component\DependencyInjection\Definition;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AutoRoutePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AutoRoutePass());
    }

    public function testRegistration()
    {
        $serviceRegistryDefinition = new Definition();
        $this->setDefinition('cmf_routing_auto.service_registry', $serviceRegistryDefinition);

        $tokenProviderDefinition = new Definition();
        $tokenProviderDefinition->addTag('cmf_routing_auto.token_provider', array('alias' => 'foobar'));
        $this->setDefinition('some_token_provider', $tokenProviderDefinition);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'cmf_routing_auto.service_registry',
            'registerTokenProvider',
            array(
                'foobar',
                new Reference('some_token_provider')
            )
        );

    }
}
