Feature: Single Language
    In order to publish my blog posts
    as a blogger
    I need to have auto routes for them

    Scenario: Persisting blog post
        Given I have no blog posts yet
        When I publish a new blog post on "2014-03-22" called "1 year Routing Auto"
        Then the route "/2014/1-year-routing-auto" should be created

    Scenario: Renaming blog post
        Given I have no blog posts yet
        And I published a blog post on "2014-03-22" called "1 year Routing Auto"
        When I rename "1 year Routing Auto" to "1 year of auto routing"
        Then the route "/2014/1-year-of-auto-routing" should be created
        And the route "/2014/1-year-routing-auto" should redirect

    Scenario: Deleting blog post
        Given I have no blog posts yet
        And I published a blog post on "2014-03-22" called "1 year Routing Auto"
        When I delete "1 year Routing Auto"
        Then the route "/2014/1-year-routing-auto" should not exists
