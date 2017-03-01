<?php

use Symfony\Component\DependencyInjection\Definition;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\ServicePass;

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
        $tokenProviderDefinition->addTag('cmf_routing_auto.token_provider', array('alias' => 'foobar'));
        $this->setDefinition('some_token_provider', $tokenProviderDefinition);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'cmf_routing_auto.service_registry',
            'registerTokenProvider',
            array(
                'foobar',
                new Reference('some_token_provider'),
            )
        );
    }
}
