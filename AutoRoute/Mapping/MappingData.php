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

/**
 * Holds the mapping information for one class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class MappingData
{
    protected $className;
    protected $urlSchema;
    protected $tokenProviders = array();

    public function __construct($className)
    {
        $this->className = $className;
    }

    public function setUrlSchema($schema)
    {
        $this->urlSchema = $schema;
    }

    public function getUrlSchema()
    {
        return $this->urlSchema;
    }

    public function addTokenProvider(TokenProvider $unit)
    {
        $this->tokenProviders[$unit->getName()] = $unit;
    }

    public function getTokenProviders()
    {
        return $this->tokenProviders;
    }

    public function getClassName()
    {
        return $this->className;
    }
}
