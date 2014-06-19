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

use Metadata\MetadataFactoryInterface;
use Metadata\Cache\CacheInterface;

/**
 * The MetadataFactory class should be used to get the metadata for a specific
 * class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class MetadataFactory implements \IteratorAggregate, MetadataFactoryInterface
{
    /** @var ClassMetadata[] */
    protected $metadatas = array();
    /** @var ClassMetadata[] */
    protected $resolvedMetadatas = array();
    /** @var null|CacheInterface */
    protected $cache;

    /**
     * @param ClassMetadata[] $metadatas Optional
     * @param CacheInterface  $cache     Optional
     */
    public function __construct(array $metadatas = array(), CacheInterface $cache = null)
    {
        $this->metadatas = $metadatas;
        $this->cache     = $cache;
    }

    /**
     * Adds an array of ClassMetadata classes.
     *
     * Caution: New ClassMetadata for the same class will be merged into the
     * existing ClassMetadata, this will override token providers for the same
     * token.
     *
     * @param ClassMetadata[] $metadatas
     */
    public function addMetadatas(array $metadatas)
    {
        foreach ($metadatas as $metadata) {
            if (isset($this->metadatas[$metadata->getClassName()])) {
                $this->metadatas[$metadata->getClassName()]->merge($metadata);
            }

            $this->metadatas[$metadata->getClassName()] = $metadata;
        }
    }

    /**
     * Tries to find the metadata for the given class.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getMetadataForClass($class)
    {
        if (!isset($this->resolvedMetadatas[$class])) {
            $this->resolveMetadata($class);
        }

        return $this->resolvedMetadatas[$class];
    }

    /**
     * Resolves the metadata of parent classes of the given class.
     *
     * @param string $class
     *
     * @throws Exception\ClassNotMappedException
     */
    protected function resolveMetadata($class)
    {
        $classFqns = class_parents($class);
        $classFqns[] = $class;
        $classFqns = $classFqns;
        $metadatas = array();
        $addedClasses = array();

        foreach ($classFqns as $classFqn) {
            foreach ($this->doResolve($classFqn, $addedClasses) as $metadata) {
                $metadatas[] = $metadata;
            }
        }

        if (0 === count($metadatas)) {
            throw new Exception\ClassNotMappedException($class);
        }

        $metadata = null;
        foreach ($metadatas as $data) {
            if (null === $metadata) {
                $metadata = $data;
            } else {
                $metadata->merge($data);
            }
        }

        $this->resolvedMetadatas[$class] = $metadata;
    }

    protected function doResolve($classFqn, array &$addedClasses)
    {
        $metadatas = array();

        if (in_array($classFqn, $addedClasses)) {
            throw new \LogicException(sprintf('Circual reference detected: %s', implode(' > ', $addedClasses).' -> '.$classFqn));
        }

        if (isset($this->metadatas[$classFqn])) {
            $currentMetadata = $this->metadatas[$classFqn];
            $addedClasses[] = $classFqn;

            if (isset($this->metadatas[$extend = $currentMetadata->getExtendedClass()])) {
                foreach ($this->doResolve($extend, $addedClasses) as $extendData) {
                    $metadatas[] = $extendData;
                }
            }
            $metadatas[] = $this->metadatas[$classFqn];
        }

        return $metadatas;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->metadatas);
    }
}
