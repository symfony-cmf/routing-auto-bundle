<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Subscriber;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;

class AutoRouteListenerTest extends BaseTestCase
{
    protected function createBlog()
    {
        $post = new Blog;
        $post->path = '/test/test-post';
        $post->title = 'Unit testing blog post';

        $this->getDm()->persist($post);
        $this->getDm()->flush();
        $this->getDm()->clear();
    }

    public function testPersistBlog()
    {
        $this->createBlog();

        $route = $this->getDm()->find(null, '/test/auto-route/posts/unit-testing-blog-post');

        $this->assertNotNull($route);

        // make sure auto-route has been persisted
        $post = $this->getDm()->find(null, '/test/test-post');
        $routes = $this->getDm()->getReferrers($post);

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('unit-testing-blog-post', $routes[0]->getName());
    }

    public function testUpdate()
    {
        $this->createBlog();

        $post = $this->getDm()->find(null, '/test/test-post');
        // test update
        $post->title = 'Foobar';
        $this->getDm()->persist($post);
        $this->getDm()->flush();

        // make sure auto-route has been persisted
        $post = $this->getDm()->find(null, '/test/test-post');
        $routes = $post->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute', $routes[0]);

        $this->getDm()->refresh($routes[0]);

        $this->assertEquals('foobar', $routes[0]->getName());
        $this->assertEquals('/test/auto-route/posts/foobar', $routes[0]->getId());

    }

    public function testRemove()
    {
        $this->createBlog();
        $post = $this->getDm()->find(null, '/test/test-post');

        // test removing
        $this->getDm()->remove($post);

        $this->getDm()->flush();

        $baseRoute = $this->getDm()->find(null, '/test/auto-route/posts');
        $routes = $this->getDm()->getChildren($baseRoute);
        $this->assertCount(0, $routes);
    }
}

