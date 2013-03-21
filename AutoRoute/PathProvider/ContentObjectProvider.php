<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\CouldNotFindRouteException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Provides path elements by determining them from
 * the existing routes of a PHPCR-ODM document returned
 * by a designated method on the Content document.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ContentObjectProvider implements PathProviderInterface
{
    protected $method;
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function init(array $options)
    {
        if (!isset($options['method'])) {
            throw new MissingOptionException(__CLASS__, 'method');
        }

        $this->method = $options['method'];
    }

    public function providePath(RouteStack $routeStack)
    {
        $context = $routeStack->getContext();

        if (count($context->getRouteStacks()) > 0) {
            throw new \RuntimeException(
                'ContentObjectProvider must belong to the first builder unit - adding '.
                'the full route path of an existing object would not make any sense otherwise.'
            );
        }

        $contentObject = $context->getContent();
        $method = $this->method;

        if (!method_exists($contentObject, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist on class "%s"', $method, get_class($contentObject)));
        }

        $object = $contentObject->$method();

        $routeFilter = function ($referrer) use ($object) {
            if ($referrer instanceof Route && $referrer->getRouteContent() === $object) {
                return true;
            }

            return false;
        };

        $referringRoutes = new ArrayCollection;

        if ($this->documentIsPersisted($object)) {
            // check to see existing routes
            $referrers = $this->dm->getReferrers($object);
            $referringRoutes = $referrers->filter($routeFilter);
        }

        // Now check to see if there are any scheduled routes
        // I think this should be handled by the ODM ...
        
        $uow = $this->dm->getUnitOfWork();
        $scheduledInserts = $uow->getScheduledInserts();
        $scheduledRoutes = array_filter($scheduledInserts, $routeFilter);
        $routes = array_merge($referringRoutes->toArray(), array_values($scheduledRoutes));

        if (count($routes) > 1) {
            throw new \RuntimeException(
                'Multiple referring routes (i.e. translations) not supported yet.'
            );
        }

        if (empty($routes)) {
            throw new CouldNotFindRouteException(sprintf(
                'Could not find route for object "%s" provided by %s:%s',
                get_class($object),
                get_class($contentObject),
                $method
            ));
        }

        $route = current($routes);
        $id = $route->getId();

        // get rid of the first path separator (We do have one right ...)
        $id = substr($id, 1);

        $pathElements = explode('/', $id);
        $routeStack->addPathElements($pathElements);
    }

    protected function documentIsPersisted($document)
    {
        $metadata = $this->dm->getClassMetadata(get_class($document));
        $id = $metadata->getIdentifierValue($document);
        $phpcrSession = $this->dm->getPhpcrSession();
        return $phpcrSession->nodeExists($id);
    }
}
