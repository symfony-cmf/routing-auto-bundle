# [WIP] Symfony CMF Routing Auto Route Bundle [![Build Status](https://secure.travis-ci.org/symfony-cmf/RoutingAutoBundle.png)](http://travis-ci.org/symfony-cmf/RoutingAutoBundle)

This bundle automatically creates and manages routes for configured persisted
document classes.

*WARNING*: This bundle is still experimental. It works, but there may be some
as-yet unknown issues. The API, however, should not change too much in the
future.

See the [official documentation](http://symfony.com/doc/master/cmf/bundles/routing-auto.html)

## Example configuration

The following is the current functional test configuration:

    symfony_cmf_routing_auto:

        auto_route_mapping:

            ##
            # e.g. /cms/auto-route/blog/my-blogs-title
            Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Blog:

                # generate or use path components leading up to the final part of the path
                content_path:
                    base:
                        provider:
                            name: specified
                            path: /test/auto-route/blog
                        exists_action:
                            strategy: use
                        not_exists_action:
                            strategy: create
                            patcher: generic

                content_name:
                    provider:
                        name: from_object_method
                        method: getTitle
                    exists_action:
                        strategy: auto_increment
                        pattern: -%d
                    not_exists_action:
                        strategy: create

## Restrictions:

 * Only documents stored with PHPCR-ODM are supported.
 * You must have the RoutingBundle installed.

## Installation

Add a requirement for ``symfony-cmf/routing-auto-bundle`` to your
composer.json and instantiate the bundle in your AppKernel.php

    new Symfony\Cmf\Bundle\RoutingAutoBundle\CmfRoutingAutoBundle()

## Running the tests

To initialize the test environment run the initialization script (you only need
to do this once):

    ./Tests/Functional/init_travis.sh

Then run all the tests with:

    phpunit -c phpunit.xml.dist
