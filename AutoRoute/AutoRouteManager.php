<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

use Doctrine\ODM\PHPCR\DocumentManager;
use Metadata\MetadataFactoryInterface;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Document\AutoRoute;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use PHPCR\Util\NodeHelper;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $mapping;

    /**
     * @param array              $mapping     Class => configuration mapping
     */
    public function __construct($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Create or update the automatically generated route for
     * the given document.
     *
     * When this is finished it will support multiple locales.
     *
     * @param object Mapped document for which to generate the AutoRoute
     *
     * @return AutoRoute
     */
    public function updateAutoRouteForDocument($document)
    {
        $metadata = $this->getMetadata($document);
        $autoRoute = $this->getAutoRouteForDocument($document);

        $autoRouteName = $this->getRouteName($document);
        $autoRoute->setName($autoRouteName);

        $autoRouteParent = $this->getParentRoute($document);
        $autoRoute->setParent($autoRouteParent);

        $this->dm->persist($autoRoute);

        return $autoRoute;
    }

    /**
     * Remove all auto routes associated with the given document.
     *
     * @param object $document Mapped document
     *
     * @todo: Test me
     *
     * @return array Array of removed routes
     */
    public function removeAutoRoutesForDocument($document)
    {
        $autoRoutes = $this->fetchAutoRoutesForDocument($document);
        foreach ($autoRoutes as $autoRoute) {
            $this->dm->remove($autoRoute);
        }

        return $autoRoutes;
    }

    /**
     * Return true if the given document is mapped with AutoRoute
     *
     * @param object $document Document
     *
     * @return boolean
     */
    public function isAutoRouteable($document)
    {
        foreach ($this->mapping as $classFqn => $metadata) {
            if ($document instanceof $classFqn) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a route name based on the designated route name method in
     * the given mapped document.
     *
     * Here we use the slugifier service given to this class to normalize
     * the title.
     *
     * @param object Mapped document
     *
     * @return string
     */
    protected function getRouteName($document)
    {
        $metadata = $this->getMetadata($document);

        $routeNameMethod = $metadata['route_method_name'];
        $routeName = $document->$routeNameMethod();
        $routeName = $this->slugifier->slugify($routeName);

        return $routeName;
    }

    /**
     * Return the parent route for the generated AutoRoute.
     *
     * Currently we check to see if a base route path has been specified
     * in the given mapped document, if not we fall back to the global default.
     *
     * @TODO: Enable dynamic parents (e.g. name-of-my-blog/my-post)
     *
     * @param object Get parent route of this mapped document.
     *
     * @return Route
     */
    protected function getParentRoute($document)
    {
        $metadata = $this->getMetadata($document);
        $defaultPath = $metadata['base_path'] ? : $this->defaultPath;

        if ($metadata['base_path_auto_create']) {
            if (!$this->phpcrSession->nodeExists($defaultPath)) {
                NodeHelper::createPath($this->phpcrSession, $defaultPath);
            }
        }

        $parent = $this->dm->find(null, $defaultPath);

        if (!$parent) {
            throw new \Exception(sprintf(
                'Could not find default route parent at path "%s"',
                $defaultPath
            ));
        }

        return $parent;
    }

    /**
     * Convenience method for retrieving Metadata.
     */
    protected function getMetadata($document)
    {
        foreach ($this->mapping as $classFqn => $metadata) {
            if ($document instanceof $classFqn) {
                return $metadata;
            }
        }
    }

    /**
     * Return the existing or a new AutoRoute for the given document.
     *
     * @throws \Exception If we have more than one
     *
     * @param object $document Mapped document that needs an AutoRoute
     *
     * @return AutoRoute
     */
    protected function getAutoRouteForDocument($document)
    {
        $autoRoutes = array();

        if ($this->isDocumentPersisted($document)) {
            $autoRoutes = $this->fetchAutoRoutesForDocument($document);
        }

        $locale = null; 

        if ($locale) {
            // filter non-matching locales, note that we could do this with the QueryBuilder
            // but currently searching array values is not supported by jackalope-doctrine-dbal.
            array_filter($res, function ($route) use ($locale) {
                if ($route->getDefault('_locale') != $locale) {
                    return false;
                }

                return true;
            });
        }

        if (count($autoRoutes) > 1) {
            throw new Exception\MoreThanOneAutoRoute($document);
        } elseif (count($autoRoutes) == 1) {
            $autoRoute = $autoRoutes->first();
        } else {
            $autoRoute = new AutoRoute;
            $autoRoute->setRouteContent($document);
        }

        return $autoRoute;
    }

    /**
     * Fetch all the automatic routes for the given document
     *
     * @param object $document Mapped document
     *
     * @return array
     */
    public function fetchAutoRoutesForDocument($document)
    {
        $routes = $this->dm->getReferrers($document, null, 'routeContent');
        $routes = $routes->filter(function ($route) {
            if ($route instanceof AutoRoute) {
                return true;
            }

            return false;
        });

        return $routes;
    }

    public function getDefaultPath()
    {
        return $this->defaultPath;
    }

    protected function isDocumentPersisted($document)
    {
        $metadata = $this->dm->getClassMetadata(get_class($document));
        $id = $metadata->getIdentifierValue($document);

        return $this->phpcrSession->nodeExists($id);
    }
}
