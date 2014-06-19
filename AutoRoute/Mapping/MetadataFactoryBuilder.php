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

/**
 * MetaFactoryBuilder
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class MetadataFactoryBuilder
{
    /** 
     * @var array 
     */
    protected $resources;

    /** 
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @param LoaderInterface $loader
     * @param array $resources
     */
    public function __construct(LoaderInterface $loader, array $resources)
    {
        $this->loader = $loader;
        $this->resources = $resources;
    }

    /**
     * Return the metadata factory
     *
     * @return MetadataFactory
     */
    public function getMetadataFactory()

    {
        $mappingFactory = new MetadataFactory();

        foreach ($this->resources as $resource) {
            $mappingFactory->addMetadatas($this->loader->load($resource['path'], $resource['type']));
        }

        return $mappingFactory;
    }
}
