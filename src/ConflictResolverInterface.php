<?php

namespace Symfony\Cmf\Component\RoutingAuto;

interface ConflictResolverInterface
{
    /**
     * If this method is called then the given URL is in
     * conflict with an existing URL and needs to be unconflicted.
     *
     * @param string $url
     *
     * @return string unconflicted URL
     */
    public function resolveConflict(UrlContext $urlContext);

}
