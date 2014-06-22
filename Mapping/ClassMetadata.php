<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Mapping;

use Metadata\MergeableInterface;
use Metadata\MergeableClassMetadata;

/**
 * Holds the metadata for one class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ClassMetadata extends MergeableClassMetadata
{
    /**
     * @var string
     */
    protected $urlSchema;

    /**
     * @var array
     */
    protected $tokenProviders = array();

    /** 
     * @var array
     */
    protected $conflictResolver = array('name' => 'throw_exception', 'options' => array());

    /**
     * Defunct route handler, default to remove
     *
     * @var array
     */
    protected $defunctRouteHandler = array('name' => 'remove');

    /**
     * @var string
     */
    protected $extendedClass;

    /**
     * Set the URL schema to use for the subject class.
     *
     * e.g. {foobar}/articles/{date}
     *
     * @param string $schema
     */
    public function setUrlSchema($schema)
    {
        $this->urlSchema = $schema;
    }

    /**
     * Return the URL schema
     *
     * @return string
     */
    public function getUrlSchema()
    {
        return $this->urlSchema;
    }

    /**
     * Add a token provider configfuration.
     *
     * @param string $tokenName
     * @param array $provider
     * @param boolean $override
     */
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

    /**
     * Return an associative array of token provider configurations.
     * Keys are the token provider names, values are configurations in
     * array format.
     *
     * @return array
     */
    public function getTokenProviders()
    {
        return $this->tokenProviders;
    }

    /**
     * Set the conflict resolver configuration.
     *
     * @param array
     */
    public function setConflictResolver($conflictResolver)
    {
        $this->conflictResolver = $conflictResolver;
    }

    /**
     * Return the conflict resolver configuration.
     *
     * @return array
     */
    public function getConflictResolver()
    {
        return $this->conflictResolver;
    }

    /**
     * Set the defunct route handler configuration.
     *
     * e.g.
     *
     *   array('remove', array('option1' => 'value1'))
     *
     * @param array
     */
    public function setDefunctRouteHandler($defunctRouteHandler)
    {
        $this->defunctRouteHandler = $defunctRouteHandler;
    }

    /**
     * Return the defunct route handler configuration
     */
    public function getDefunctRouteHandler()
    {
        return $this->defunctRouteHandler;
    }

    /**
     * Extend the metadata of the mapped class with given $name
     *
     * @param string $name
     */
    public function setExtendedClass($name)
    {
        $this->extendedClass = $name;
    }

    /**
     * Return the name of the extended class (if any)
     *
     * @return string
     */
    public function getExtendedClass()
    {
        return $this->extendedClass;
    }

    /**
     * Return the name of the subject class
     *
     * @return string
     */
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

        if ($defunctRouteHandler = $metadata->getDefunctRouteHandler()) {
            $this->setDefunctRouteHandler($defunctRouteHandler);
        }

        if ($conflictResolver = $metadata->getConflictResolver()) {
            $this->setConflictResolver($conflictResolver);
        }
    }
}
