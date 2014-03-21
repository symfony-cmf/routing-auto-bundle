<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext;

/**
 * Interface for class which handles URL generation and conflict resolution
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface UrlGeneratorInterface
{
    /**
     * Generate a URL for the given document
     *
     * @param object $document
     *
     * @return string
     */
    public function generateUrl(UrlContext $urlContext);

    /**
     * The given URL already exists in the database, this method
     * should delegate the task of resolving the conflict to
     * the ConflictResolver configured in the mapping for the
     * document.
     *
     * @param object $document
     * @param string $url
     *
     * @return string
     */
    public function resolveConflict(UrlContext $urlContext);
}
