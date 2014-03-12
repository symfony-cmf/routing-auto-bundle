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
        $metadatas = array();
        $addedClasses = array();

        foreach ($classFqns as $classFqn) {
            if (isset($this->metadatas[$classFqn])) {
                if (in_array($classFqn, $addedClasses)) {
                    throw new \LogicException(sprintf('Circual reference detected: %s', implode(' > ', $addedClasses).' -> '.$classFqn));
                }
                $currentMetadata = $this->metadatas[$classFqn];

                if (!in_array($extend = $currentMetadata->getExtendedClass(), $addedClasses) && isset($this->metadatas[$extend])) {
                    $metadatas[] = $this->metadatas[$extend];
                    $addedClasses[] = $extend;
                }
                $metadatas[] = $this->metadatas[$classFqn];
                $addedClasses[] = $classFqn;
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

    public function getIterator()
    {
        return new \ArrayIterator($this->metadatas);
    }
}
