<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Repository;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\RepositoryInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Blog;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Post;

class DoctrinePhpcrOdm implements RepositoryInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function init()
    {
        $session = $this->container->get('doctrine_phpcr.session');

        if ($session->nodeExists('/test')) {
            $session->getNode('/test')->remove();
        }

        if (!$session->nodeExists('/test')) {
            $session->getRootNode()->addNode('test', 'nt:unstructured');
            $session->getNode('/test')->addNode('auto-route');
        }

        $session->save();
    }

    public function createBlog($withPosts = false)
    {
        $blog = new Blog();
        $blog->path = '/test/test-blog';
        $blog->title = 'Unit testing blog';

        $this->getObjectManager()->persist($blog);

        if ($withPosts) {
            $post = new Post();
            $post->name = 'This is a post title';
            $post->title = 'This is a post title';
            $post->blog = $blog;
            $post->date = new \DateTime('2013/03/21');
            $this->getObjectManager()->persist($post);
        }

        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();
    }

    public function getObjectManager()
    {
        return $this->container->get('doctrine_phpcr')->getManager();
    }

    public function findBlog($blogName)
    {
        return $this->getObjectManager()->find(null, '/test/'.$blogName);
    }

    public function findRoutesForBlog(Blog $blog)
    {
        return $this->getObjectManager()->getReferrers($blog);
    }

    public function findAutoRoute($url)
    {
        return $this->getObjectManager()->find(null, '/test/auto-route'.$url);
    }
}
