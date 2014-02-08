<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\CouldNotFindRouteException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute;

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

        if (!$object) {
            throw new \RuntimeException(sprintf(
                'The %s:%s method has returned an empty value "%s"',
                get_class($contentObject),
                $method,
                var_export($object, true)
            ));
        }

        $routeFilter = function ($referrer) use ($object, $context) {
            if ($referrer instanceof AutoRoute && $referrer->getContent() === $object) {

                // filter the referrering routes by locale
                if ($referrer->getLocale() == $context->getLocale()) {
                    return true;
                }
            }

            return false;
        };

        $referringRoutes = new ArrayCollection();

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
                'Found more than one referring auto route, this should not happen.'
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
