<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
        $loader->load('token_providers.xml');
        $loader->load('defunct_route_handlers.xml');
        $loader->load('conflict_resolvers.xml');

        $config = $processor->processConfiguration($configuration, $configs);

        $resources = array();

        // auto mapping
        if ($config['auto_mapping']) {
            $bundles = $container->getParameter('kernel.bundles');
            $resources = $this->findMappingFiles($bundles);
        }

        // add configured mapping file resources
        if (isset($config['mapping']['resources'])) {
            foreach ($config['mapping']['resources'] as $resource) {
                $resources[] = $resource;
            }
        }
        $container->setParameter('cmf_routing_auto.metadata.loader.resources', $resources);

        $hasProvider = false;
        if ($this->isConfigEnabled($container, $config['persistence']['phpcr'])) {
            $hasProvider = true;
            $loader->load('phpcr-odm.xml');
            $container->setParameter('cmf_routing_auto.persistence.phpcr.route_basepath', $config['persistence']['phpcr']['route_basepath']);
        }

        if (!$hasProvider) {
            throw new InvalidConfigurationException(
                'The RoutingAutoBundle requires that you enable one of the persistence layers in your application configuration. ' .
                'See the documentation for more information'
            );
        }
    }

    protected function findMappingFiles($bundles)
    {
        $resources = array();
        foreach ($bundles as $bundle) {
            $refl = new \ReflectionClass($bundle);
            $bundlePath = dirname($refl->getFileName());
            foreach (array('xml', 'yml') as $extension) {
                $path = $bundlePath.'/Resources/config/cmf_routing_auto.'.$extension;
                if (file_exists($path)) {
                    $resources[] = array('path' => $path, 'type' => null);
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
