<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Factory as BaseFactory;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitChain;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnit;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * The container aware implementation of the auto route factory.
 *
 * This improves the performance, as builder services are lazy loaded.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ContainerAwareFactory extends BaseFactory implements ContainerAwareInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Register an alias for a service ID of the specified type.
     *
     * @param string $type
     * @param string $alias
     * @param string $serviceId
     */
    public function registerAlias($type, $alias, $serviceId)
    {
        if (!isset($this->builderServices[$type])) {
            throw new \InvalidArgumentException(sprintf('Unknown builder service type "%s"', $type));
        }

        $this->builderServices[$type][$alias] = $serviceId;
    }

    /**
     * Gets the builder service.
     *
     * @param string $type
     * @param string $alias
     */
    protected function getBuilderService($type, $alias)
    {
        $serviceId = $this->builderServices[$type][$alias];

        return $this->container->get($serviceId);
    }
}
