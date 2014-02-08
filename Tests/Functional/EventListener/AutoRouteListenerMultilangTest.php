<?php

namespace Functional\EventListener;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Category;

class AutoRouteListenerMultilangTest extends BaseTestCase
{
    public function provideMultilangCategory()
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
    public function testMultilangCategory($data, $expectedPaths)
    {
        $category = new Category();
        $category->path = '/test/category1';
        $this->getDm()->persist($category);

        foreach (array(
            'de' => 'Meine neue Kategorie',
            'fr' => 'Ma nouvelle catégorie',
            'en' => 'My new category',
        ) as $locale => $title) {
            $category->title = $title;
            $this->getDm()->bindTranslation($category, $locale);
        }
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
                    'test/auto-route/articles/en/category/hello-everybody',
                    'test/auto-route/articles/fr/category/bonjour-le-monde',
                    'test/auto-route/articles/de/category/gutentag',
                    'test/auto-route/articles/es/category/hola-todo-el-mundo',
                ),
            ),
        );
    }

    /**
     * @dataProvider provideMultilangArticle
     */
    public function testMultilangArticle($data, $expectedPaths)
    {
        $category = new Category();
        $category->path = '/test/category1';
        $category->title = 'category';
        $this->getDm()->persist($category);
        foreach (array_keys($data) as $lang) {
            $this->getDm()->bindTranslation($category, $lang);
        }

        $article = new Article;
        $article->path = '/test/article-1';
        $article->categories[] = $category;

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

            $this->assertNotNull($route, 'Found route with path ' . $expectedPath);
            $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $route);

            $content = $route->getContent();

            $this->assertNotNull($content);
            $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article', $content);

            // We havn't loaded the translation for the document, so it is always in the default language
            $this->assertEquals('Hello everybody!', $content->title);
        }
    }
}
