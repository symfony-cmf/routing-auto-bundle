<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\AdapterPass;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\ServicePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CmfRoutingAutoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ServicePass());
        $container->addCompilerPass(new AdapterPass());
        $this->buildPhpcrCompilerPass($container);
    }

    /**
     * Creates and registers compiler passes for PHPCR-ODM mapping if both the
     * phpcr-odm and the phpcr-bundle are present.
     *
     * @param ContainerBuilder $container
     */
    private function buildPhpcrCompilerPass(ContainerBuilder $container)
    {
        if (!class_exists('Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass')
            || !class_exists('Doctrine\ODM\PHPCR\Version')
        ) {
            return;
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['CmfRoutingBundle'])) {
            $container->addCompilerPass(
                DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                    [
                        realpath(__DIR__.'/Resources/config/doctrine-model') => 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model',
                    ],
                    ['cmf_routing_auto.persistence.phpcr.manager_name'],
                    false,
                    ['CmfRoutingAutoBundle' => 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model']
                )
            );
        }
    }
}
