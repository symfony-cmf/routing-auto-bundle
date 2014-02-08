<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\PersistentCollection;

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
     * @PHPCR\Referrers(
     *   referringDocument="Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Category",
     *   referencedBy="articles"
     * )
     */
    public $categories = array();

    /**
     * @PHPCR\Locale()
     */
    public $locale;

    public function getCategory()
    {
        return current($this->categories);
    }

    public function getTitle()
    {
        return $this->title;
    }
}
