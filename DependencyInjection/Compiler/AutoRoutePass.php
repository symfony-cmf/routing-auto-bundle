<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRoutePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'cmf_routing_auto.factory'
        )) {
            return;
        }

        $builderUnitChainFactory = $container->getDefinition(
            'cmf_routing_auto.factory'
        );

        $types = array(
            'provider',
            'exists_action',
            'not_exists_action',
            'route_maker'
        );

        foreach ($types as $type) {
            $ids = $container->findTaggedServiceIds('cmf_routing_auto.'.$type);
            foreach ($ids as $id => $attributes) {
                if (!isset($attributes[0]['alias'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'No "alias" specified for auto route "%s" service: "%s"',
                        str_replace('_', ' ', $type),
                        $id
                    ));
                }

                $builderUnitChainFactory->addMethodCall(
                    'registerAlias',
                    array($type, $attributes[0]['alias'], new Reference($id)));
            }
        }
    }
}
