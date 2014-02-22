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

/**
 * @PHPCR\Document(translator="child", referenceable=true)
 *
 * @CmfRoutingAuto\UrlSchema("/posts/%article.title%)
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

    /**
     * @CmfRoutingAuto\Provider(name="article.title", { "slugify": true })
     */
    public function getTitle()
    {
        return $this->title;
    }
}
