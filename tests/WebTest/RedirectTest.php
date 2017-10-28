<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\WebTest;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Document\SeoArticle;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;

class RedirectTest extends BaseTestCase
{
    public function setUp(array $options = [], $routebase = null)
    {
        $this->client = $this->createClient();

        parent::setUp($options, $routebase);

        $article = new SeoArticle();
        $article->title = 'SEO Article';
        $article->path = '/test/article-1';

        $this->getDm()->persist($article);
        $this->getDm()->flush();
    }

    public function testRedirect()
    {
        $article = $this->getDm()->find(null, '/test/article-1');
        $this->assertNotNull($article);
        $article->title = 'Renamed Article';
        $this->getDm()->flush();

        $this->client->request('GET', '/seo-articles/seo-article');
        $resp = $this->client->getResponse();
        $this->assertEquals(302, $resp->getStatusCode());
        $this->assertContains('Redirecting to <a href="/seo-articles/renamed-article">/seo-articles/renamed-article</a>', $resp->getContent());
    }
}
