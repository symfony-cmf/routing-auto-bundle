<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\OperationStack;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface DefunctRouteHandlerInterface
{
    /**
     * Handle auto routes which refer to the given
     * document but which do not correspond to the URLs
     * generated.
     *
     * These routes are defunct - they are routes which
     * have used to be used to directly reference the
     * content, but which must now either be deleted
     * or perhaps replaced with a redirect route, or indeed
     * left alone to continue depending on the configuration.
     *
     * TODO
     */
    public function handleDefunctRoutes(OperationStack $operationStack);
}
