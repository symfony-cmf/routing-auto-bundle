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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * The MappingFactory class should be used to get the mappings for a specific 
 * class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class MappingFactory implements \IteratorAggregate
{
    /** @var MappingData[] */
    protected $mappings = array();

    public function __construct(array $mappings = array())
    {
        $this->mappings = $mappings;
    }

    /**
     * Adds an array of MappingData classes.
     *
     * @param MappingData[] $mappings
     *
     * @todo Handle duplicate mappings: Override, extend, ?
     */
    public function addMappings(array $mappings)
    {
        foreach ($mappings as $mapping) {
            $this->mappings[$mapping->getClassName()] = $mapping;
        }
    }

    /**
     * Tries to find the mappings for the given class.
     *
     * @param string $class
     *
     * @return MappingData
     *
     * @throws Exception\ClassNotMappedException
     */
    public function getMappingsForClass($class)
    {
        $classFqns = class_parents($class);
        $classFqns[] = $class;
        $classFqns = array_reverse($classFqns);

        foreach ($classFqns as $classFqn) {
            if (isset($this->mapping[$classFqn])) {
                return $this->mapping[$classFqn];
            }
        }

        throw new Exception\ClassNotMappedException($class);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->mappings);
    }
}
