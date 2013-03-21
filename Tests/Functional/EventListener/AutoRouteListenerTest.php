<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Subscriber;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Post;

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
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('unit-testing-blog', $routes[0]->getName());
    }

    public function testUpdateBlog()
    {
        $this->createBlog();

        $blog = $this->getDm()->find(null, '/test/test-blog');
        // test update
        $blog->title = 'Foobar';
        $this->getDm()->persist($blog);
        $this->getDm()->flush();

        $blog = $this->getDm()->find(null, '/test/test-blog');
        $routes = $blog->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute', $routes[0]);

        $this->getDm()->refresh($routes[0]);

        $this->assertEquals('foobar', $routes[0]->getName());
        $this->assertEquals('/test/auto-route/blog/foobar', $routes[0]->getId());
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
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute', $routes[0]);
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
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('this-is-different', $routes[0]->getName());
    }
}
