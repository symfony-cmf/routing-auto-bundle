# [WIP] Symfony CMF Routing Auto Route Bundle [![Build Status](https://secure.travis-ci.org/symfony-cmf/RoutingAutoRouteBundle.png)](http://travis-ci.org/symfony-cmf/RoutingExtraBundle)

This bundle automatically creates and manages routes for given persisted document classes.

Use cases:

 * Blog posts: To be able to view a blog posts, blog posts must have a route which 
               is appended to that of its parent, the blog.

               - When a Post is created an auto-route is automatically created.
               - When the Post is updated, the auto-route is updated.
               - When the Post is deleted, the auto-route is deleted.

Restrictions:

 * Only documents stored with PHPCR-ODM are supported.
 * You must have the RoutingExtraBundle installed.
