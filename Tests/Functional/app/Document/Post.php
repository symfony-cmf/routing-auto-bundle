<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Testdoc;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\Annotations as CMFRouting;

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
     * @PHPCR\Referrers(filter="routeContent")
     */
    public $routes;

    /**
     * @PHPCR\String()
     */
    public $title;

    public function getTitle()
    {
        return $this->title;
    }
}

