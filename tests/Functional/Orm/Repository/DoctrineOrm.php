<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Orm\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NoResultException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Orm\AutoRoute;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\BlogNoTranslatable;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\Post;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\PostNoTranslatable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DoctrineOrm.
 *
 * @author WAM Team <develop@wearemarketing.com>
 */
class DoctrineOrm
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function init()
    {
    }

    public function createBlogNoTranslatable($withPosts = false)
    {
        $blog = new BlogNoTranslatable();
        $blog->setTitle('Unit testing blog');

        if ($withPosts) {
            $post = new PostNoTranslatable();
            $post->setTitle('This is a post title');
            $post->setBlog($blog);
            $post->setDate(new \DateTime('2013/03/21'));
            $this->getObjectManager()->persist($post);
        }

        $this->getObjectManager()->persist($blog);

        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();
    }

    public function createBlog($withPosts = false)
    {
        $blog = new Blog();
        $blog->setTitle('Unit testing blog');
        $blog->mergeNewTranslations();

        $this->getObjectManager()->persist($blog);

        if ($withPosts) {
            $post = new Post();
            $post->setTitle('This is a post title');
            $post->setBlog($blog);
            $post->setDate(new \DateTime('2013/03/21'));
            $post->mergeNewTranslations();
            $this->getObjectManager()->persist($post);
        }

        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();
    }

    public function getObjectManager()
    {
        return $this->container->get('doctrine')->getManager();
    }

    public function findBlogNoTranslatable($blogName)
    {
        $objectManager = $this->getObjectManager();
        $objectManager->clear();

        $blog = $objectManager
            ->getRepository(BlogNoTranslatable::class)
            ->findOneByTitle($blogName);

        return $blog;
    }

    public function findBlog($blogName)
    {
        $objectManager = $this->getObjectManager();

        $query = $objectManager->createQuery("
            SELECT b FROM Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\Blog b 
            INNER JOIN Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\BlogTranslation bt
            WHERE bt.title = :title
        ");

        $query->setParameter(':title', $blogName);
        $blog = $query->getSingleResult();

        return $blog;
    }

    public function findRoutesForBlog($blog)
    {
        $this->getObjectManager()->clear();

        try {
            $blog = $this->findBlog($blog->getTitle());
            $routes = $blog->getRoutes();
        } catch (NoResultException $e) {
            return [];
        }

        return $routes;
    }

    public function findAutoRoute($url)
    {
        $repository = $this
            ->getObjectManager()
            ->getRepository(AutoRoute::class);

        return $repository->findOneBy([
            'staticPrefix' => $url,
        ]);
    }

    public function findPostNoTranslatable($title)
    {
        $objectManager = $this->getObjectManager();
        $objectManager->clear();

        $post = $objectManager
            ->getRepository(PostNoTranslatable::class)
            ->findOneByTitle($title);

        return $post;
    }

    public function findPost($title)
    {
        $objectManager = $this->getObjectManager();
        $query = $objectManager->createQuery("
            SELECT p FROM Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\Post p 
            INNER JOIN Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\PostTranslation pt
            WHERE pt.title = :title
        ");

        $query->setParameter(':title', $title);
        $post = $query->getSingleResult();

        return $post;
    }

    public function findRoutesForPost(Post $post)
    {
        $repository = $this
            ->getObjectManager()
            ->getRepository(AutoRoute::class);

        $contentId = ['id' => $post->getId()];

        $routes = $repository->findBy([
            'contentClass' => get_class($post),
        ]);

        $routesCollection = new ArrayCollection($routes);
        $routes = $routesCollection->filter(function ($route) use ($contentId) {
            if ($route->getContentId() === $contentId) {
                return true;
            }

            return false;
        });

        return $routes->toArray();
    }

    public function findRoutesForPostNoTranslatable(PostNoTranslatable $post)
    {
        $repository = $this
            ->getObjectManager()
            ->getRepository(AutoRoute::class);

        $contentId = ['id' => $post->getId()];

        $routes = $repository->findBy([
            'contentClass' => get_class($post),
        ]);

        $routesCollection = new ArrayCollection($routes);
        $routes = $routesCollection->filter(function ($route) use ($contentId) {
            if ($route->getContentId() === $contentId) {
                return true;
            }

            return false;
        });

        return $routes->toArray();
    }

    public function findContent(AutoRoute $route)
    {
        $repository = $this->getObjectManager()->getRepository($route->getContentClass());
        $object = $repository->find($route->getContentId()['id']);

        return $object;
    }

    public function findArticle($title, $locale)
    {
        $objectManager = $this->getObjectManager();
        $query = $objectManager->createQuery("
            SELECT a FROM Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\Article a 
            INNER JOIN Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\ArticleTranslation at
            WHERE at.title = :title
                AND at.locale = :locale
        ");

        $query->setParameter(':title', $title);
        $query->setParameter(':locale', $locale);
        $article = $query->getSingleResult();

        return $article;
    }

    public function findRoutesForArticle($article)
    {
        $repository = $this
            ->getObjectManager()
            ->getRepository(AutoRoute::class);

        $contentId = ['id' => $article->getId()];

        $routes = $repository->findBy([
            'contentClass' => get_class($article),
        ]);

        $routesCollection = new ArrayCollection($routes);
        $routes = $routesCollection->filter(function ($route) use ($contentId) {
            if ($route->getContentId() === $contentId) {
                return true;
            }

            return false;
        });

        return $routes->toArray();
    }
}
