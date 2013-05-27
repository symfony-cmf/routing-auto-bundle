<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\App\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use Symfony\Cmf\Bundle\RoutingBundle\Mapping\Annotations as CMFRouting;

/**
 * @PHPCR\Document(
 *      referenceable=true
 * )
 */
class Post
{
    /**
     * @PHPCR\Id()
     */
    public $path;

    /**
     * @PHPCR\Referrers(
     *   referringDocument="Symfony\Cmf\Bundle\RoutingBundle\Document\Route", 
     *   referencedBy="routeContent"
     * )
     */
    public $routes;

    /**
     * @PHPCR\ParentDocument()
     */
    public $blog;

    /**
     * @PHPCR\NodeName()
     */
    public $title;

    /**
     * @PHPCR\String
     */
    public $body;

    public function getTitle()
    {
        return $this->title;
    }

    public function getBlog()
    {
        return $this->blog;
    }

    public function getDate()
    {
        return new \DateTime('2013/03/21');
    }
}
