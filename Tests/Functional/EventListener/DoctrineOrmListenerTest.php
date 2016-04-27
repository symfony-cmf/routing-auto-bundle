<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\EventListener;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Post;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\ConcreteContent;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\SeoArticleMultilang;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\SeoArticle;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Page;

class DoctrineOrm extends ListenerTestCase
{
    public function getKernelConfiguration()
    {
        return [
            'environment' => 'doctrine_orm',
        ];
    }
}
