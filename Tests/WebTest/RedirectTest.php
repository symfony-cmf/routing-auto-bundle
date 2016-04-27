<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\WebTest;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\SeoArticle;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;

class RedirectTest extends BaseTestCase
{
    public function getKernelConfiguration()
    {
        return [
            'environment' => 'doctrine_phpcr_odm',
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $article = new SeoArticle();
        $article->title = 'SEO Article';
        $article->path = '/test/article-1';

        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();
    }

    public function testRedirect()
    {
        $article = $this->getObjectManager()->find(null, '/test/article-1');
        $this->assertNotNull($article);
        $article->title = 'Renamed Article';
        $this->getObjectManager()->flush();

        $this->client->request('GET', '/seo-articles/seo-article');
        $resp = $this->client->getResponse();
        $this->assertEquals(302, $resp->getStatusCode());
        $this->assertContains('Redirecting to <a href="/seo-articles/renamed-article">/seo-articles/renamed-article</a>', $resp->getContent());
    }
}
