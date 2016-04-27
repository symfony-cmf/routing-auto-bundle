<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\EventListener;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Post;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\ConcreteContent;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\SeoArticleMultilang;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\SeoArticle;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Page;

class PhpcrOdmRouteListenerTest extends ListenerTestCase
{
    public function getKernelConfiguration()
    {
        return [
            'environment' => 'doctrine_phpcr_odm',
        ];
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
        $this->getRepository()->createBlog($withPosts);

        $blog = $this->getObjectManager()->find(null, '/test/test-blog');
        // test update
        $blog->title = 'Foobar';
        $this->getObjectManager()->persist($blog);
        $this->getObjectManager()->flush();

        // note: The NAME stays the same, its the ID not the title
        $blog = $this->getObjectManager()->find(null, '/test/test-blog');
        $this->assertNotNull($blog);

        $routes = $blog->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);

        $this->assertEquals('foobar', $routes[0]->getName());
        $this->assertEquals('/test/auto-route/blog/foobar', $routes[0]->getId());

        if ($withPosts) {
            $post = $this->getObjectManager()->find(null, '/test/test-blog/This is a post title');
            $this->assertNotNull($post);

            $routes = $post->routes;

            $this->assertNotNull($routes[0]);
            $this->getObjectManager()->refresh($routes[0]);

            $this->assertEquals('/test/auto-route/blog/foobar/2013/03/21/this-is-a-post-title', $routes[0]->getId());
        }
    }

    public function testUpdatePostNotChangingTitle()
    {
        $this->getRepository()->createBlog(true);

        $post = $this->getObjectManager()->find(null, '/test/test-blog/This is a post title');
        $this->assertNotNull($post);

        $post->body = 'Test';

        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $post = $this->getObjectManager()->find(null, '/test/test-blog/This is a post title');
        $routes = $post->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);

        $this->assertEquals('this-is-a-post-title', $routes[0]->getName());
    }

    public function testRemoveBlog()
    {
        $this->getRepository()->createBlog();
        $blog = $this->getObjectManager()->find(null, '/test/test-blog');

        // test removing
        $this->getObjectManager()->remove($blog);

        $this->getObjectManager()->flush();

        $baseRoute = $this->getObjectManager()->find(null, '/test/auto-route/blog');
        $routes = $this->getObjectManager()->getChildren($baseRoute);
        $this->assertCount(0, $routes);
    }

    public function testPersistPost()
    {
        $this->getRepository()->createBlog(true);
        $route = $this->getObjectManager()->find(null, '/test/auto-route/blog/unit-testing-blog/2013/03/21/this-is-a-post-title');
        $this->assertNotNull($route);

        // make sure auto-route references content
        $post = $this->getObjectManager()->find(null, '/test/test-blog/This is a post title');
        $routes = $this->getObjectManager()->getReferrers($post);

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);
        $this->assertEquals('this-is-a-post-title', $routes[0]->getName());
    }

    public function testUpdatePost()
    {
        $this->getRepository()->createBlog(true);

        // make sure auto-route references content
        $post = $this->getObjectManager()->find(null, '/test/test-blog/This is a post title');
        $post->title = 'This is different';

        // test for issue #52
        $post->date = new \DateTime('2014-01-25');

        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();

        $routes = $this->getObjectManager()->getReferrers($post);

        $this->assertCount(1, $routes);
        $route = $routes[0];

        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $route);
        $this->assertEquals('this-is-different', $route->getName());

        $node = $this->getObjectManager()->getNodeForDocument($route);
        $this->assertEquals(
            '/test/auto-route/blog/unit-testing-blog/2014/01/25/this-is-different',
            $node->getPath()
        );
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

                    'test/auto-route/articles/en/hello-everybody-edit',
                    'test/auto-route/articles/fr/bonjour-le-monde-edit',
                    'test/auto-route/articles/de/gutentag-edit',
                    'test/auto-route/articles/es/hola-todo-el-mundo-edit',

                    'test/auto-route/articles/en/hello-everybody-review',
                    'test/auto-route/articles/fr/bonjour-le-monde-review',
                    'test/auto-route/articles/de/gutentag-review',
                    'test/auto-route/articles/es/hola-todo-el-mundo-review',
                ),
            ),
        );
    }

    /**
     * @dataProvider provideMultilangArticle
     */
    public function testMultilangArticle($data, $expectedPaths)
    {
        $article = new Article();
        $article->path = '/test/article-1';
        $this->getObjectManager()->persist($article);

        foreach ($data as $lang => $title) {
            $article->title = $title;
            $this->getObjectManager()->bindTranslation($article, $lang);
        }

        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $locales = array_keys($data);

        foreach ($expectedPaths as $i => $expectedPath) {
            $localeIndex = $i % count($locales);
            $expectedLocale = $locales[$localeIndex];

            $route = $this->getObjectManager()->find(null, $expectedPath);

            $this->assertNotNull($route, 'Route: '.$expectedPath);
            $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $route);
            $this->assertEquals($expectedLocale, $route->getAutoRouteTag());

            $content = $route->getContent();

            $this->assertNotNull($content);
            $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article', $content);

            // We havn't loaded the translation for the document, so it is always in the default language
            $this->assertEquals('Hello everybody!', $content->title);
        }
    }

    public function provideUpdateMultilangArticle()
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
                    'test/auto-route/articles/de/gutentag-und-auf-wiedersehen',
                    'test/auto-route/articles/es/hola-todo-el-mundo',
                ),
            ),
        );
    }

    public function testMultilangArticleRemainsSameLocale()
    {
        $article = new Article();
        $article->path = '/test/article-1';
        $article->title = 'Good Day';
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();

        $article->title = 'Hello everybody!';
        $this->getObjectManager()->bindTranslation($article, 'en');

        $article->title = 'Bonjour le monde!';
        $this->getObjectManager()->bindTranslation($article, 'fr');

        // let current article be something else than the last bound locale
        $this->getObjectManager()->findTranslation(get_class($article), $this->getObjectManager()->getUnitOfWork()->getDocumentId($article), 'en');

        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $this->assertEquals('Hello everybody!', $article->title);
    }

    /**
     * @dataProvider provideUpdateMultilangArticle
     */
    public function testUpdateMultilangArticle($data, $expectedPaths)
    {
        $article = new Article();
        $article->path = '/test/article-1';
        $this->getObjectManager()->persist($article);

        foreach ($data as $lang => $title) {
            $article->title = $title;
            $this->getObjectManager()->bindTranslation($article, $lang);
        }

        $this->getObjectManager()->flush();

        $article_de = $this->getObjectManager()->findTranslation('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article', '/test/article-1', 'de');
        $article_de->title .= '-und-auf-wiedersehen';
        $this->getObjectManager()->bindTranslation($article_de, 'de');
        $this->getObjectManager()->persist($article_de);

        $this->getObjectManager()->flush();

        $article_de = $this->getObjectManager()->findTranslation('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article', '/test/article-1', 'de');
        $routes = $this->getObjectManager()->getReferrers($article_de);

        // Multiply the expected paths by 3 because Article has 3 routes defined.
        $this->assertCount(count($data) * 3, $routes);

        $this->getObjectManager()->clear();

        foreach ($expectedPaths as $expectedPath) {
            $route = $this->getObjectManager()->find(null, $expectedPath);

            $this->assertNotNull($route);
            $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $route);

            $content = $route->getContent();

            $this->assertNotNull($content);
            $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article', $content);

            // We havn't loaded the translation for the document, so it is always in the default language
            $this->assertEquals('Hello everybody!', $content->title);
        }
    }

    public function provideLeaveRedirect()
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
                    'en' => 'Goodbye everybody!',
                    'fr' => 'Aurevoir le monde!',
                    'de' => 'Auf weidersehn',
                    'es' => 'Adios todo el mundo',
                ),
                array(
                    'test/auto-route/seo-articles/en/hello-everybody',
                    'test/auto-route/seo-articles/fr/bonjour-le-monde',
                    'test/auto-route/seo-articles/de/gutentag',
                    'test/auto-route/seo-articles/es/hola-todo-el-mundo',
                ),
                array(
                    'test/auto-route/seo-articles/en/goodbye-everybody',
                    'test/auto-route/seo-articles/fr/aurevoir-le-monde',
                    'test/auto-route/seo-articles/de/auf-weidersehn',
                    'test/auto-route/seo-articles/es/adios-todo-el-mundo',
                ),
            ),
        );
    }

    /**
     * @dataProvider provideLeaveRedirect
     */
    public function testLeaveRedirect($data, $updatedData, $expectedRedirectRoutePaths, $expectedAutoRoutePaths)
    {
        $article = new SeoArticleMultilang();
        $article->title = 'Hai';
        $article->path = '/test/article-1';
        $this->getObjectManager()->persist($article);

        foreach ($data as $lang => $title) {
            $article->title = $title;
            $this->getObjectManager()->bindTranslation($article, $lang);
        }

        $this->getObjectManager()->flush();

        foreach ($updatedData as $lang => $title) {
            $article = $this->getObjectManager()->findTranslation('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\SeoArticleMultilang', '/test/article-1', $lang);
            $article->title = $title;
            $this->getObjectManager()->bindTranslation($article, $lang);
        }

        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();

        foreach ($expectedRedirectRoutePaths as $originalPath) {
            $redirectRoute = $this->getObjectManager()->find(null, $originalPath);
            $this->assertNotNull($redirectRoute, 'Redirect exists for: '.$originalPath);
            $this->assertEquals(AutoRouteInterface::TYPE_REDIRECT, $redirectRoute->getDefault('type'));
        }

        foreach ($expectedAutoRoutePaths as $newPath) {
            $autoRoute = $this->getObjectManager()->find(null, $newPath);
            $this->assertNotNull($autoRoute, 'Autoroute exists for: '.$newPath);
            $this->assertEquals(AutoRouteInterface::TYPE_PRIMARY, $autoRoute->getDefault('type'));
        }
    }

    /**
     * @depends testLeaveRedirect
     *
     * See https://github.com/symfony-cmf/RoutingAutoBundle/issues/111
     */
    public function testLeaveRedirectAndRenameToOriginal()
    {
        $article = new SeoArticle();
        $article->title = 'Hai';
        $article->path = '/test/article-1';
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();

        $article->title = 'Ho';
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();

        $article->title = 'Hai';
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();
    }

    /**
     * Leave direct should migrate children.
     */
    public function testLeaveRedirectChildrenMigrations()
    {
        $article1 = new SeoArticle();
        $article1->title = 'Hai';
        $article1->path = '/test/article-1';
        $this->getObjectManager()->persist($article1);
        $this->getObjectManager()->flush();

        // add a child to the route
        $parentRoute = $this->getObjectManager()->find(null, '/test/auto-route/seo-articles/hai');
        $childRoute = new AutoRoute();
        $childRoute->setName('foo');
        $childRoute->setParent($parentRoute);
        $this->getObjectManager()->persist($childRoute);
        $this->getObjectManager()->flush();

        $article1->title = 'Ho';
        $this->getObjectManager()->persist($article1);
        $this->getObjectManager()->flush();

        $originalRoute = $this->getObjectManager()->find(null, '/test/auto-route/seo-articles/hai');
        $this->assertNotNull($originalRoute);
        $this->assertCount(0, $this->getObjectManager()->getChildren($originalRoute));

        $newRoute = $this->getObjectManager()->find(null, '/test/auto-route/seo-articles/ho');
        $this->assertNotNull($newRoute);
        $this->assertCount(1, $this->getObjectManager()->getChildren($newRoute));
    }

    /**
     * Ensure that we can map parent classes: #56.
     */
    public function testParentClassMapping()
    {
        $content = new ConcreteContent();
        $content->path = '/test/content';
        $content->title = 'Hello';
        $this->getObjectManager()->persist($content);
        $this->getObjectManager()->flush();

        $this->getObjectManager()->refresh($content);

        $routes = $content->routes;

        $this->assertCount(1, $routes);
    }

    public function testConflictResolverAutoIncrement()
    {
        $this->getRepository()->createBlog();
        $blog = $this->getObjectManager()->find(null, '/test/test-blog');

        $post = new Post();
        $post->name = 'Post 1';
        $post->title = 'Same Title';
        $post->blog = $blog;
        $post->date = new \DateTime('2013/03/21');
        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();

        $post = new Post();
        $post->name = 'Post 2';
        $post->title = 'Same Title';
        $post->blog = $blog;
        $post->date = new \DateTime('2013/03/21');
        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();

        $post = new Post();
        $post->name = 'Post 3';
        $post->title = 'Same Title';
        $post->blog = $blog;
        $post->date = new \DateTime('2013/03/21');
        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();

        $expectedRoutes = array(
            '/test/auto-route/blog/unit-testing-blog/2013/03/21/same-title',
            '/test/auto-route/blog/unit-testing-blog/2013/03/21/same-title-1',
            '/test/auto-route/blog/unit-testing-blog/2013/03/21/same-title-2',
        );

        foreach ($expectedRoutes as $expectedRoute) {
            $route = $this->getObjectManager()->find('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $expectedRoute);
            $this->assertNotNull($route);
        }
    }

    public function testCreationOfChildOnRoot()
    {
        $page = new Page();
        $page->title = 'Home';
        $page->path = '/test/home';
        $this->getObjectManager()->persist($page);
        $this->getObjectManager()->flush();

        $expectedRoute = '/test/auto-route/home';
        $route = $this->getObjectManager()->find('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $expectedRoute);

        $this->assertNotNull($route);
    }

    /**
     * @expectedException Symfony\Cmf\Component\RoutingAuto\ConflictResolver\Exception\ExistingUriException
     */
    public function testConflictResolverDefaultThrowException()
    {
        $blog = new Blog();
        $blog->path = '/test/test-blog';
        $blog->title = 'Unit testing blog';
        $this->getObjectManager()->persist($blog);
        $this->getObjectManager()->flush();

        $blog = new Blog();
        $blog->path = '/test/test-blog-the-second';
        $blog->title = 'Unit testing blog';
        $this->getObjectManager()->persist($blog);
        $this->getObjectManager()->flush();
    }

    public function testGenericNodeShouldBeConvertedInAnAutoRouteNode()
    {
        $blog = new Blog();
        $blog->path = '/test/my-post';
        $blog->title = 'My Post';
        $this->getObjectManager()->persist($blog);
        $this->getObjectManager()->flush();

        $this->assertInstanceOf(
            'Doctrine\ODM\PHPCR\Document\Generic',
            $this->getObjectManager()->find(null, '/test/auto-route/blog')
        );
        $blogRoute = $this->getObjectManager()->find(null, '/test/auto-route/blog/my-post');
        $this->assertInstanceOf('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface', $blogRoute);
        $this->assertSame($blog, $blogRoute->getContent());

        $page = new Page();
        $page->path = '/test/blog';
        $page->title = 'Blog';

        $this->getObjectManager()->persist($page);
        $this->getObjectManager()->flush();

        $this->assertInstanceOf(
            'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface',
            $this->getObjectManager()->find(null, '/test/auto-route/blog')
        );
        $this->assertInstanceOf(
            'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface',
            $this->getObjectManager()->find(null, '/test/auto-route/blog/my-post')
        );
    }
}
