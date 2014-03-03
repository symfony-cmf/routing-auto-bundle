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

    public function setUrlSchema($schema)
    {
        $this->urlSchema = $schema;
    }

    public function getUrlSchema()
    {
        return $this->urlSchema;
    }

    public function addTokenProvider(TokenProvider $unit, $override = false)
    {
        if (!$override && isset($this->tokenProvider[$unit->getName()])) {
            throw new \InvalidArgumentException(sprintf('Class "%s" already has a token provider called "%s", set the second argument of addTokenProvider to true to override it.', $this->name, $unit->getName()));
        }

        $this->tokenProviders[$unit->getName()] = $unit;
    }

    public function getTokenProviders()
    {
        return $this->tokenProviders;
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
     * @param ClassMetadata $metadata
     */
    public function merge(MergeableInterface $metadata)
    {
        parent::merge($metadata);

        foreach ($metadata->getTokenProviders() as $tokenProvider) {
            $this->addTokenProvider($tokenProvider, true);
        }
    }
}
