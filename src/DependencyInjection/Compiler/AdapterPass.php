<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AdapterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'cmf_routing_auto.auto_route_manager'
        )) {
            return;
        }

        $adapter = $container->getParameter('cmf_routing_auto.adapter_name');
        $adapterId = null;
        $adapterNames = [];
        $ids = $container->findTaggedServiceIds('cmf_routing_auto.adapter');

        foreach ($ids as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new \InvalidArgumentException(sprintf(
                    'No "name" specified for auto route adapter "%s"',
                    $id
                ));
            }

            $alias = $attributes[0]['alias'];
            $adapterNames[] = $alias;
            if ($adapter === $alias) {
                $adapterId = $id;

                break;
            }
        }

        if (null === $adapterId) {
            throw new \RuntimeException(sprintf(
                'Could not find configured adapter "%s", available adapters: "%s"',
                $adapter,
                implode('", "', $adapterNames)
            ));
        }

        $managerDef = $container->getDefinition('cmf_routing_auto.auto_route_manager');
        $container->setAlias('cmf_routing_auto.adapter', $adapterId);
        $container->getAlias('cmf_routing_auto.adapter')->setPublic(true);
    }
}
