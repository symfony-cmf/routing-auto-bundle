<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * The context class for the PHPCR storage layer.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class PHPCRContext implements SnippetAcceptingContext
{
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
     * @Given I have no blog posts yet
     */
    public function iHaveNoBlogPostsYet()
    {
        throw new PendingException();
    }

    /**
     * @When I publish a new blog post on :date called :title
     * @Given I published a blog post on :date called :title
     */
    public function publishBlogPost($title, $date)
    {
        throw new PendingException();
    }

    /**
     * @When I rename :oldTitle to :newTitle
     */
    public function renameBlogPost($oldTitle, $newTitle)
    {
        throw new PendingException();
    }

    /**
     * @When I delete :title
     */
    public function deleteBlogPost($title)
    {
        throw new PendingException();
    }

    /**
     * @Then the route :path should be created
     */
    public function assertRouteCreated($path)
    {
        throw new PendingException();
    }

    /**
     * @Then the route :path should redirect
     */
    public function assertRouteRedirects($path)
    {
        throw new PendingException();
    }

    /**
     * @Then the route :path should not exists
     */
    public function assertRouteNotExists($path)
    {
        throw new PendingException();
    }
}
