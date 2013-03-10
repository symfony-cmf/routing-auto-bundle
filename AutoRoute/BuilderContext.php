<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderContext
{
    protected $urlParts = array();

    public function addUrlPart(string $part)
    {
        $this->urlParts[] = $part;
    }

    public function getPath()
    {
        return implode('/', $this->urlParts);
    }
}
