<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class CmfRoutingAutoExtension extends Extension
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
        $loader->load('path_provider.xml');
        $loader->load('exists_action.xml');
        $loader->load('not_exists_action.xml');
        $loader->load('route_maker.xml');

        $config = $processor->processConfiguration($configuration, $configs);
        $chainFactoryDef = $container->getDefinition('cmf_routing_auto.factory');

        // normalize configuration
        foreach ($config['mappings'] as $classFqn => $config) {
            $chainFactoryDef->addMethodCall('registerMapping', array($classFqn, $config));
        }
    }

    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/routing_auto';
    }
}

