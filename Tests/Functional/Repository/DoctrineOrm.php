<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Repository;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\RepositoryInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\Blog;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Post;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;

class DoctrineOrm implements RepositoryInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function init()
    {
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
        return $this->container->get('doctrine')->getManager();
    }

    public function findBlog($blogName)
    {
        return $this->getObjectManager()->getRepository(Blog::class)->findOneBy([
            'title' => $blogName
        ]);
    }

    public function findRoutesForBlog($blog)
    {
        return [];
    }

    public function findAutoRoute($url)
    {
        return $this->getObjectManager()->getRepository(Route::class)->findBy([
            'staticPrefix' => $url
        ]);
    }
}
