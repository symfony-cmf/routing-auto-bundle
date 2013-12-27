<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use Symfony\Cmf\Bundle\RoutingBundle\Mapping\Annotations as CMFRouting;

/**
 * @PHPCR\Document(
 *      referenceable=true
 * )
 */
class Page
{
    /**
     * @PHPCR\ParentDocument()
     */
    public $parent;

    /**
     * @PHPCR\NodeName()
     */
    public $name;

    /**
     * @PHPCR\Referrers(
     *   referringDocument="Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route",
     *   referencedBy="content"
     * )
     */
    public $routes;

    /**
     * @PHPCR\String
     */
    public $body;

    public function getName()
    {
        return $this->name;
    }

    public function getBody()
    {
        return $this->body;
    }
}

