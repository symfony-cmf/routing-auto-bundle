<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Command;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Post;
use Symfony\Component\Console\Input\ArrayInput;

class RefreshCommandTest extends BaseTestCase
{
    public function setUp()
    {
        $this->createBlog(true);
    }

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

    public function testCommand()
    {
        $application = $this->getApplication();
        $input = new ArrayInput;
        $application->run($input);
    }
}

