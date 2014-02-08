<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Subscriber;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Post;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute;

class AutoRouteListenerTest extends BaseTestCase
{
    protected function createBlog($withPosts = false)
    {
        $blog = new Blog;
        $blog->path = '/test/test-blog';
        $blog->title = 'Unit testing blog';

        $this->getDm()->persist($blog);

        if ($withPosts) {
            $post = new Post;
            $post->title = 'This is a post title';
            $post->blog = $blog;
            $this->getDm()->persist($post);
        }

        $this->getDm()->flush();
        $this->getDm()->clear();
    }

    public function testPersistBlog()
    {
        $this->createBlog();

        $route = $this->getDm()->find(null, '/test/auto-route/blog/unit-testing-blog');

        $this->assertNotNull($route);

        // make sure auto-route has been persisted
        $blog = $this->getDm()->find(null, '/test/test-blog');
        $routes = $this->getDm()->getReferrers($blog);

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);
        $this->assertEquals('unit-testing-blog', $routes[0]->getName());
    }

    public function provideTestUpdateBlog()
    {
        return array(
            array(false),
            array(true),
        );
    }

    /**
     * @dataProvider provideTestUpdateBlog
     */
    public function testUpdateRenameBlog($withPosts = false)
    {
        $this->createBlog($withPosts);

        $blog = $this->getDm()->find(null, '/test/test-blog');
        // test update
        $blog->title = 'Foobar';
        $this->getDm()->persist($blog);
        $this->getDm()->flush();

        // note: The NAME stays the same, its the ID not the title
        $blog = $this->getDm()->find(null, '/test/test-blog');
        $this->assertNotNull($blog);

        $routes = $blog->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);

        $this->assertEquals('foobar', $routes[0]->getName());
        $this->assertEquals('/test/auto-route/blog/foobar', $routes[0]->getId());

        if ($withPosts) {
            $post = $this->getDm()->find(null, '/test/test-blog/This is a post title');
            $this->assertNotNull($post);

            $routes = $post->routes;
            $this->getDm()->refresh($routes[0]);

            $this->assertEquals('/test/auto-route/blog/foobar/2013/03/21/this-is-a-post-title', $routes[0]->getId());
        }
    }

    public function testUpdatePostNotChangingTitle()
    {
        $this->createBlog(true);

        $post = $this->getDm()->find(null, '/test/test-blog/This is a post title');
        $this->assertNotNull($post);

        $post->body = 'Test';

        $this->getDm()->persist($post);
        $this->getDm()->flush();
        $this->getDm()->clear();

        $post = $this->getDm()->find(null, '/test/test-blog/This is a post title');
        $routes = $post->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);

        $this->assertEquals('this-is-a-post-title', $routes[0]->getName());
    }

    public function testRemoveBlog()
    {
        $this->createBlog();
        $blog = $this->getDm()->find(null, '/test/test-blog');

        // test removing
        $this->getDm()->remove($blog);

        $this->getDm()->flush();

        $baseRoute = $this->getDm()->find(null, '/test/auto-route/blog');
        $routes = $this->getDm()->getChildren($baseRoute);
        $this->assertCount(0, $routes);
    }

    public function testPersistPost()
    {
        $this->createBlog(true);
        $route = $this->getDm()->find(null, '/test/auto-route/blog/unit-testing-blog/2013/03/21/this-is-a-post-title');
        $this->assertNotNull($route);

        // make sure auto-route references content
        $post = $this->getDm()->find(null, '/test/test-blog/This is a post title');
        $routes = $this->getDm()->getReferrers($post);

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);
        $this->assertEquals('this-is-a-post-title', $routes[0]->getName());
    }

    public function testUpdatePost()
    {
        $this->createBlog(true);

        // make sure auto-route references content
        $post = $this->getDm()->find(null, '/test/test-blog/This is a post title');
        $post->title = "This is different";
        $this->getDm()->persist($post);
        $this->getDm()->flush();

        $routes = $this->getDm()->getReferrers($post);

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);
        $this->assertEquals('this-is-different', $routes[0]->getName());
    }

    public function provideMultilangArticle()
    {
        return array(
            array(
                array(
                    'en' => 'Hello everybody!',
                    'fr' => 'Bonjour le monde!',
                    'de' => 'Gutentag',
                    'es' => 'Hola todo el mundo',
                ),
                array(
                    'test/auto-route/articles/en/hello-everybody',
                    'test/auto-route/articles/fr/bonjour-le-monde',
                    'test/auto-route/articles/de/gutentag',
                    'test/auto-route/articles/es/hola-todo-el-mundo',
                ),
            ),
        );
    }

    /**
     * @dataProvider provideMultilangArticle
     */
    public function testMultilangArticle($data, $expectedPaths)
    {
        $article = new Article;
        $article->path = '/test/article-1';
        $this->getDm()->persist($article);

        foreach ($data as $lang => $title) {
            $article->title = $title;
            $this->getDm()->bindTranslation($article, $lang);
        }

        $this->getDm()->flush();
        $this->getDm()->clear();

        $articleTitles = array_values($data);
        foreach ($expectedPaths as $i => $expectedPath) {
            $route = $this->getDm()->find(null, $expectedPath);

            $this->assertNotNull($route);
            $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $route);

            $content = $route->getContent();

            $this->assertNotNull($content);
            $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article', $content);

            // We havn't loaded the translation for the document, so it is always in the default language
            $this->assertEquals('Hello everybody!', $content->title);
        }
    }
}
