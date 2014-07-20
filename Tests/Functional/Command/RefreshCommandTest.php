<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Command;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Post;

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
            $post->name = 'This is a post title';
            $post->title = 'This is a post title';
            $post->body = 'Test Body';
            $post->blog = $blog;
            $post->date = new \DateTime('2013/03/21');
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
            'foo:bar'
        ));
        $output = new NullOutput();
        //$output = new StreamOutput(fopen('php://stdout', 'w'));
        $command = new RefreshCommand();
        $command->setApplication($application);
        $command->run($input, $output);
    }
}
