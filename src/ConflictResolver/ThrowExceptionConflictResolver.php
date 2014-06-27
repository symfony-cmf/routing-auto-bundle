<?php

namespace Symfony\Cmf\Component\RoutingAuto\ConflictResolver;

use Symfony\Cmf\Component\RoutingAuto\ConflictResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\UrlContext;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;

/**
 * This conflcit resolver "resolves" conflicts by throwing exceptions.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ThrowExceptionConflictResolver implements ConflictResolverInterface
{
    /**
     * {@inheritDoc}
     */
    public function resolveConflict(UrlContext $urlContext)
    {
        $url = $urlContext->getUrl();

        throw new Exception\ExistingUrlException(sprintf(
            'There already exists an auto route for URL "%s" and the system is configured ' . 
            'to throw this exception in this case. Alternatively you can choose to use a ' .
            'different strategy, for example, auto incrementation. Please refer to the ' .
            'documentation for more information.',
            $url
        ));
    }
}

