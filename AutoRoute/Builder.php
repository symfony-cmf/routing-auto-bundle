<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

use PHPCR\SessionInterface as PhpcrSession;

/**
 * This class uses the actions defined builder units construct
 * a path.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Builder
{
    protected $phpcrSession;

    public function __construct(PhpcrSession $phpcrSession)
    {
        $this->phpcrSession = $phpcrSession;
    }

    public function build(BuilderUnitInterface $builderUnit, BuilderContext $context)
    {
        $builderUnit->pathAction($context);

        $lastRoute = $context->getLastRoute();

        $exists = $this->phpcrSession->nodeExists($context->getPath()); 

        if ($exists) {
            $builderUnit->existsAction($context);
        }

        // Only do the notExists action if the last path has not changed
        // (i.e. the exists action hasn't already provided a route)
        if ($lastRoute === $context->getLastRoute()) {
            $builderUnit->notExistsAction($context);
        }
    }
}
