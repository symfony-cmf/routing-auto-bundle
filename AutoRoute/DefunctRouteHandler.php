<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\OperationStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MetadataFactory;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\ServiceRegistry;

/**
 * Class which takes actions on routes which are left behind
 * after a content changes its URL.
 *
 * For examples:
 *
 * - Leave a redirect route
 * - Delete the route
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DefunctRouteHandler implements DefunctRouteHandlerInterface
{
    protected $serviceRegistry;
    protected $adapter;

    /**
     * @param ServiceRegistry auto routing service registry (for getting old route action)
     * @param AdapterInterface auto routing backend adapter
     * @param MetadataFactory  auto routing metadata factory
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        AdapterInterface $adapter,
        ServiceRegistry $serviceRegistry
    )
    {
        $this->serviceRegistry = $serviceRegistry;
        $this->adapter = $adapter;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function handleDefunctRoutes(OperationStack $operationStack)
    {
        $referrerCollection = $this->adapter->getReferringRoutes($operationStack->getSubjectObject());

        foreach ($referrerCollection as $referrer) {
            if (false === $operationStack->containsRoute($referrer)) {
                $urlContexts = $operationStack->getUrlContexts();
                if (!$urlContexts) {
                    continue;
                }

                $canonicalRoute = $urlContexts[0]->getNewRoute();

                if (!$canonicalRoute) {
                    continue;
                }

                $this->adapter->removeDefunctRoute($referrer, $canonicalRoute);
            }
        }
    }
}
