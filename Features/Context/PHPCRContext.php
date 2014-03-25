<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Features\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use PHPCR\Util\NodeHelper;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Post;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The context class for the PHPCR storage layer.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class PHPCRContext implements SnippetAcceptingContext, KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    protected $kernel;
    protected $blog;
    protected $article;

    /**
     * Initializes context.
     *
     * Every scenario gets it's own context object.
     * You can also pass arbitrary arguments to the context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given the database is empty
     */
    public function theDatabaseIsEmpty()
    {
        NodeHelper::purgeWorkspace($this->getDm()->getPhpcrSession());
        NodeHelper::createPath($this->getDm()->getPhpcrSession(), '/test/auto-route');

        // run the initializers
        $initializer = $this->getContainer()->get('doctrine_phpcr.initializer_manager');
        $initializer->initialize();
    }

    /**
     * @Given there is a blog named :title
     */
    public function createBlog($title)
    {
        $this->blog = $blog = new Blog();
        $blog->path = '/test/'.trim(strtolower(preg_replace('/[^[:alnum:]]+/', '-', $title)), '-');
        $blog->title = $title;

        $this->getDm()->persist($blog);
        $this->getDm()->flush();
    }

    /**
     * @When I publish a new article in multiple languages with the following titles:
     * @Given I published an article in multiple languages with the following titles:
     */
    public function publishArticle(TableNode $table)
    {
        $this->article = $article = new Article;
        $article->path = '/test/article-1';

        $this->getDm()->persist($article);

        $hash = $table->getHash();
        foreach ($hash as $row) {
            $article->title = $row['title'];
            $this->getDm()->bindTranslation($article, $row['language']);
        }

        $this->getDm()->flush();
    }

    /**
     * @When I publish a new blog post on :date called :title
     * @Given I published a blog post on :date called :title
     */
    public function publishBlogPost($title, $date)
    {
        $post = new Post;
        $post->title = $title;
        $post->blog = $this->blog;
        $post->date = new \DateTime($date);

        $this->getDm()->persist($post);
        $this->getDm()->flush();
    }

    /**
     * @When I rename :oldTitle to :newTitle
     */
    public function renameBlogPost($oldTitle, $newTitle)
    {
        $post = $this->getDm()->find(null, $this->blog->path.'/'.$oldTitle);

        $post->title = $newTitle;

        $this->getDm()->persist($post);
        $this->getDm()->flush();
    }

    /**
     * @When I delete :title
     */
    public function deleteBlogPost($title)
    {
        $this->getDm()->remove($this->getDm()->find(null, $this->blog->path.'/'.$title));
        $this->getDm()->flush();
    }

    /**
     * @When I rename locale :locale to :newTitle
     */
    public function iRenameLocaleTo($locale, $newTitle)
    {
        $article = $this->getDm()->findTranslation(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Article',
            '/test/article-1',
            $locale
        );

        $article->title = $newTitle;

        $this->getDm()->bindTranslation($article, $locale);
        $this->getDm()->persist($article);
        $this->getDm()->flush();
    }

    /**
     * @When I delete the article
     */
    public function iDeleteTheArticle()
    {
        $this->getDm()->remove($this->article);
        $this->getDm()->flush();
    }

    /**
     * @Then the route :path should be created
     */
    public function assertRouteCreated($path)
    {
        \PHPUnit_Framework_Assert::assertNotNull($this->getDm()->find(null, '/test/auto-route'.$path));
    }

    /**
     * @Then the route :path should redirect
     */
    public function assertRouteRedirects($path)
    {
        $route = $this->getDm()->find(null, '/test/auto-route'.$path);
        \PHPUnit_Framework_Assert::assertInstanceOf('Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute', $route);
    }

    /**
     * @Then the route :path should not exists
     */
    public function assertRouteNotExists($path)
    {
        \PHPUnit_Framework_Assert::assertEquals(null, $this->getDm()->find(null, '/test/auto-route'.$path));
    }

    /**
     * {@inheritDoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }

    protected function getDm()
    {
        return $this->getContainer()->get('doctrine_phpcr')->getManager();
    }
}
