<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping;

/**
 * @author Wouter J <wouter@wouterj.nl>
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface MappingFactoryInterface
{
    /**
     * Tries to find the mappings for the given class.
     *
     * @param string $class
     *
     * @return MappingData
     *
     * @throws Exception\ClassNotMappedException
     */
    public function getMetadataForClass($className);
}
