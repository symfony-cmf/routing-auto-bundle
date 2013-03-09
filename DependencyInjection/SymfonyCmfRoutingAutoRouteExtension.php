<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SymfonyCmfRoutingAutoRouteExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('auto_route.xml');

        $config = $processor->processConfiguration($configuration, $configs);

        $keys = array(
            'base_path',
            'auto_route_by_class'
        );

        foreach ($keys as $key) {
            $container->setParameter('symfony_cmf_routing_auto_route.'.$key, $config[$key]);
        }
    }
}

