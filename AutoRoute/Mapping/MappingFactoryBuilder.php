<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping;

use Symfony\Component\Config\Loader\LoaderInterface;

class MappingFactoryBuilder
{
    /** @var array */
    protected $resources;
    /** @var LoaderInterface */
    protected $loader;

    public function __construct(LoaderInterface $loader, array $resources)
    {
        $this->loader = $loader;
        $this->resources = $resources;
    }

    public function getMappingFactory()
    {
        $mappingFactory = new MappingFactory();

        foreach ($this->resources as $resource) {
            $mappingFactory->addMappings($this->loader->load($resource));
        }

        return $mappingFactory;
    }
}
