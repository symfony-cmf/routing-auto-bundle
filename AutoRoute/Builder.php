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

        $exists = $this->phpcrSession->nodeExists($context->getPath()); 

        if ($exists) {
            do {
                $builderUnit->existsAction($context);
            } while ($this->phpcrSession->nodeExists($context->getPath()));
        } else {
            $builderUnit->notExistsAction($context);
        }
    }
}
