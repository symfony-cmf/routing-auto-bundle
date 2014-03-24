Feature: Multiple Languages
    In order to make my posts available for everyone
    As a blogger
    I need to have multiple language auto routes

    Background:
        Given the database is empty

    Scenario: Publishing blog post
        When I publish a new article in multiple languages with the following titles:
            | language | title              |
            | en       | Hello everybody!   |
            | fr       | Bonjour le monde!  |
            | de       | Gutentag           |
            | es       | Hola todo el mundo |
        Then the route "/articles/en/hello-everybody" should be created
        And the route "/articles/fr/bonjour-le-monde" should be created
        And the route "/articles/de/gutentag" should be created
        And the route "/articles/es/hola-todo-el-mundo" should be created

    Scenario: Renaming one title
        Given I published an article in multiple languages with the following titles:
            | language | title              |
            | en       | Hello everybody!   |
            | fr       | Bonjour le monde!  |
            | de       | Gutentag           |
            | es       | Hola todo el mundo |
        When I rename locale "de" to "Gutentag und auf wiedersehen"
        Then the route "/articles/de/gutentag-und-auf-wiedersehen" should be created
        And the route "/articles/de/gutentag" should not exists

    Scenario: Deleting blog post
        Given I published an article in multiple languages with the following titles:
            | language | title              |
            | en       | Hello everybody!   |
            | fr       | Bonjour le monde!  |
            | de       | Gutentag           |
            | es       | Hola todo el mundo |
        When I delete the article
        Then the route "/articles/en/hello-everybody" should not exists
        And the route "/articles/fr/bonjour-le-monde" should not exists
        And the route "/articles/de/gutentag" should not exists
        And the route "/articles/es/hola-todo-el-mundo" should not exists
