<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use Symfony\Cmf\Bundle\RoutingBundle\Mapping\Annotations as CMFRouting;

/**
 * @PHPCR\Document(translator="child", referenceable=true)
 */
class Article
{
    /**
     * @PHPCR\Id()
     */
    public $path;

    /**
     * @PHPCR\Referrers(
     *   referringDocument="Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route",
     *   referencedBy="content"
     * )
     */
    public $routes;

    /**
     * @PHPCR\String(translated=true)
     */
    public $title;

    /**
     * @PHPCR\Locale()
     */
    public $locale;

    public function getTitle()
    {
        return $this->title;
    }
}

