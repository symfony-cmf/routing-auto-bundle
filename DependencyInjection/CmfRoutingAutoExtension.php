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

        $config = $processor->processConfiguration($configuration, $configs);

        $resources = array();

        // auto mapping
        if ($config['auto_mapping']) {
            $resources = $this->findMappingFiles($container->getParameter('kernel.bundles'));
        }

        // add configured mapping file resources
        foreach ($config['mapping']['resources'] as $path) {
            $resources[] = $path;
        }
        $container->setParameter('cmf_routing_auto.metadata.loader.resources', $resources);
    }

    protected function findMappingFiles($bundles)
    {
        $resources = array();
        foreach ($bundles as $bundle) {
            foreach (array('xml', 'yml') as $extension) {
                if (file_exists($bundles->getPath().'/Resources/config/auto_routing.'.$extension)) {
                    $resources[] = $extension;
                }
            }
        }

        return $resources;
    }

    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/routing_auto';
    }
}
