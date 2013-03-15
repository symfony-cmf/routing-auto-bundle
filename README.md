# [WIP] Symfony CMF Routing Auto Route Bundle [![Build Status](https://secure.travis-ci.org/symfony-cmf/RoutingAutoBundle.png)](http://travis-ci.org/symfony-cmf/RoutingExtraBundle)

This bundle is a WIP to automatically creates and manages routes for configured persisted 
document classes.

## Example configuration

The following is the current functional test configuration:

    symfony_cmf_routing_auto_route:

        auto_route_definitions:

            ## 
            # e.g. /cms/auto-route/blog/my-blogs-title
            Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Blog:
                chain:
                    base:
                        path_provider: 
                            name: specified
                            path: /cms/auto-route/blog
                        exists_action: 
                            strategy: use
                        not_exists_action: 
                            strategy: create
                    blog_title:
                        path_provider: 
                            name: from_object_method
                            method: getTitle
                        exists_action: 
                            strategy: auto_increment
                        not_exists_action: 
                            strategy: create

            ##
            # e.g. /cms/auto-route/blog/my-blogs-title/2013-04-09/my-post-title
            Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\app\Document\Post:
                chain:

                    # /cms/auto-route/blog/my-blogs-title
                    blog_path:
                        path_provider:
                            name: specified
                            from_method: getBlogRoutePath
                        exists_action: 
                            strategy: use
                        not_exists_action: 
                            strategy: throw_exception

                    # 2013-04-09
                    date:
                        path_provider: 
                            name: date
                            format: yyyy-mm-dd
                            from_method: getPublishedAt
                        exists_action: 
                            strategy: use
                        not_exists_action: 
                            strategy: create

                    # my-post-title
                    post_title:
                        path_provider: 
                            name: slugify_object_method
                            from_method: getTitle
                            slugfier: symfony_cmf_routing_auto_route.default_slugifier
                        exists_action: 
                            strategy: auto_increment
                            pattern: -%d
                        not_exists_action: 
                            strategy: create


## Restrictions:

 * Only documents stored with PHPCR-ODM are supported.
 * You must have the RoutingExtraBundle installed.

## TODO

There are lots of todos but these shouldn't be forgotten:

 * Change the Doctrine event subscriber to an event listener.

