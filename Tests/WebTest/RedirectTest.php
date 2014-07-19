<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\WebTest;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\SeoArticle;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;

class RedirectTest extends BaseTestCase
{
    public function setUp(array $options = array(), $routebase = null)
    {
        parent::setUp($options, $routebase);
        $this->client = $this->createClient();

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
