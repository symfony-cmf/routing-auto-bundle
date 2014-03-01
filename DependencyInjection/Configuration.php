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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * Returns the config tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('cmf_routing_auto')
            ->children()
                ->booleanNode('auto_mapping')->defaultTrue()->end()
                ->arrayNode('mapping')
                    ->fixXmlConfig('path')
                    ->children()
                        ->arrayNode('paths')
                            ->prototype('scalar')->end()
                        ->end() // directories
                    ->end()
                ->end() // mapping
            ->end();

        return $treeBuilder;
    }
}
