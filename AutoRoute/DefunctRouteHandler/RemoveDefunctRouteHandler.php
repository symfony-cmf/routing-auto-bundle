<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandlerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface;

class RemoveDefunctRouteHandler implements DefunctRouteHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritDoc}
     */
    public function handleDefunctRoutes(UrlContextStack $urlContextStack)
    {
        error_log('START');
        $referrerCollection = $this->adapter->getReferringRoutes($urlContextStack->getSubjectObject());

        $urlContextStack->dump();

        foreach ($referrerCollection as $referrer) {
            error_log('REF: '.$referrer->getId());
            if (false === $urlContextStack->containsRoute($referrer)) {

                // HERE -- how to transfer the children of the defunct route to
                //         the correct new route??

                $urlContexts = $urlContextStack->getUrlContexts();
                
                foreach ($urlContexts as $urlContext) {
                    $route = $urlContext->getRoute();

                    $this->adapter->removeDefunctRoute($referrer, $route);
                }
            }
        }
    }
}
