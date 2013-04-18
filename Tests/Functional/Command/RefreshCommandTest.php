<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Command;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Post;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Command\RefreshCommand;
use Symfony\Component\Console\Output\StreamOutput;

class RefreshCommandTest extends BaseTestCase
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

    public function testCommand()
    {
        $this->createBlog(true);

        $application = $this->getApplication();
        $input = new ArrayInput(array(
        ));;
        $output = new NullOutput();
        //$output = new StreamOutput(fopen('php://stdout', 'w'));
        $command = new RefreshCommand();
        $command->setApplication($application);
        $command->execute($input, $output);
    }
}

