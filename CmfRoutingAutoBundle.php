<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\ServicePass;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\AdapterPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

class CmfRoutingAutoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ServicePass());
        $container->addCompilerPass(new AdapterPass());
        $this->buildPhpcrCompilerPass($container);
        $this->buildOrmCompilerPass($container);
    }

    /**
     * Creates and registers compiler passes for PHPCR-ODM mapping if both the
     * phpcr-odm and the phpcr-bundle are present.
     *
     * @param ContainerBuilder $container
     */
    private function buildPhpcrCompilerPass(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['CmfRoutingBundle']) || !isset($bundles['DoctrinePhpcrBundle'])) {
            return;
        }

        $container->addCompilerPass(
            DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                array(
                    realpath(__DIR__.'/Resources/config/doctrine-model') => 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model',
                ),
                array('cmf_routing_auto.persistence.phpcr.manager_name'),
                false,
                array('CmfRoutingAutoBundle' => 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model')
            )
        );
    }

    /**
     * Creates and registers compiler passes for ORM mappings if both doctrine
     * ORM and a suitable compiler pass implementation are available.
     *
     * @param ContainerBuilder $container
     */
    private function buildOrmCompilerPass(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['CmfRoutingBundle']) || !isset($bundles['DoctrineBundle'])) {
            return;
        }

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver(
                array(
                    realpath(__DIR__ . '/Resources/config/doctrine-model') => 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model',
                ),
                array('cmf_routing_auto.dynamic.persistence.orm.manager_name'),
                false,
                array('CmfRoutingAutoBundle' => 'Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Orm')
            )
        );
    }
}
