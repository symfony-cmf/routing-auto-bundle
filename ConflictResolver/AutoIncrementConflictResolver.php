<?php

namespace Symfony\Cmf\Component\RoutingAuto\ConflictResolver;

use Symfony\Cmf\Component\RoutingAuto\ConflictResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\UrlContext;
use Symfony\Cmf\Component\RoutingAuto\Adapter\AdapterInterface;

/**
 * This conflict resolver will generate candidate URLs by appending 
 * a number to the URL. It will keep incrementing this number until
 * the URL does not exist.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoIncrementConflictResolver implements ConflictResolverInterface
{
    protected $adapter;
    protected $inc;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveConflict(UrlContext $urlContext)
    {
        $this->inc = 0;

        $url = $urlContext->getUrl();
        $candidateUrl = $this->incrementUrl($url);

        while ($route = $this->adapter->findRouteForUrl($candidateUrl)) {
            $candidateUrl = $this->incrementUrl($url);
        }

        return $candidateUrl;
    }

    protected function incrementUrl($url)
    {
        return sprintf('%s-%s', $url, ++$this->inc);
    }
}
