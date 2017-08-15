Changelog
=========

2.0.0-RC3 (unreleased)
----------------------

* PhpcrOdmAdapter now updates the content document with the new route, if the 
  content implements RouteReferrersInterface.

2.0.0-RC1
---------

* Added Symfony 3 support
* [BC BREAK] Removed all `*.class` parameters.
* Convert non-managed intermediate nodes into AutoRoute documents #165
* Added support for multiple routes on the same document

1.1.0
-----

Released.

1.0.0
-----

* Enabled new `container` token provider - retrieve URL elements
  from container parameters.

1.0.0-RC1
---------

* Removed hard dependency on PHPCR-ODM

* The RoutingAutoBundle has been almost completely rewritten and split into a component and a bundle package.
  See the documentation for the full set of changes. To migrate from PHPCR Shell you need to migrate the RoutingAuto document:

  UPDATE [nt:unstructured] SET phpcr:class="Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute" WHERE phpcr:class="Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute"

  And regenerate the routes:

  $ php app/console cmf:routing:auto:refresh

* **2014-06-06**: Updated to PSR-4 autoloading

* **2013-01-25**: Upgraded to the CMF bundle standards.
                    - Changed namespace of AutoRoute document
                    - Changed namespace of AutoRouteListener

* **2013-12-08**: Major configuration format changes.
                  See the documentation: http://symfony.com/doc/current/cmf/bundles/routing_auto.html

1.0.0-alpha4
------------

* **2013-07-31**: Updated to work with latest versions of all dependencies
