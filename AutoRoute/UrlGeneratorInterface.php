<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

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
    public function generateUrl($document);

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
    public function resolveConflict($document, $url);
}
