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

use Metadata\MergeableInterface;
use Metadata\MergeableClassMetadata;

/**
 * Holds the metadata for one class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ClassMetadata extends MergeableClassMetadata
{
    protected $urlSchema;
    protected $tokenProviders = array();
    /** @var null|array */
    protected $conflictResolver;
    protected $extend;

    public function setUrlSchema($schema)
    {
        $this->urlSchema = $schema;
    }

    public function getUrlSchema()
    {
        return $this->urlSchema;
    }

    public function addTokenProvider($tokenName, array $provider = array(), $override = false)
    {
        if ('schema' === $tokenName) {
            throw new \InvalidArgumentException(sprintf('Class "%s" has an invalid token name "%s": schema is a reserved token name.', $this->name, $tokenName));
        }

        if (!$override && isset($this->tokenProvider[$tokenName])) {
            throw new \InvalidArgumentException(sprintf('Class "%s" already has a token provider for token "%s", set the third argument of addTokenProvider to true to override it.', $this->name, $tokenName));
        }

        $this->tokenProviders[$tokenName] = $provider;
    }

    public function getTokenProviders()
    {
        return $this->tokenProviders;
    }

    public function setConflictResolver($conflictResolver)
    {
        $this->conflictResolver = $conflictResolver;
    }

    public function getConflictResolver()
    {
        return $this->conflictResolver;
    }

    public function hasConflictResolver()
    {
        return null !== $this->conflictResolver;
    }

    public function setExtendedClass($name)
    {
        $this->extend = $name;
    }

    public function getExtendedClass()
    {
        return $this->extend;
    }

    public function getClassName()
    {
        return $this->name;
    }

    /**
     * Merges another ClassMetadata into the current metadata.
     *
     * Caution: the registered token providers will be overriden when the new 
     * ClassMetadata has a token provider with the same name.
     *
     * The URL schema will be overriden, you can use {parent} to refer to the 
     * previous URL schema.
     *
     * @param ClassMetadata $metadata
     */
    public function merge(MergeableInterface $metadata)
    {
        parent::merge($metadata);

        $this->urlSchema = str_replace('{parent}', $this->urlSchema, $metadata->getUrlSchema());

        foreach ($metadata->getTokenProviders() as $tokenName => $provider) {
            $this->addTokenProvider($tokenName, $provider, true);
        }
    }

    public function serialize()
    {
        return serialize(array(
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->urlSchema,
            $this->tokenProviders,
            $this->conflictResolver,
            // extend isn't cached, since only resolved metadatas are cached. 
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->urlSchema,
            $this->tokenProviders,
            $this->conflictResolver
        ) = unserialize($str);

        $this->reflection = new \ReflectionClass($this->name);
    }
}
