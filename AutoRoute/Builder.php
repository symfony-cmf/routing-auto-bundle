<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use PHPCR\SessionInterface as PhpcrSession;

/**
 * This class uses the actions defined builder units construct
 * a path.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Builder implements BuilderInterface
{
    protected $phpcrSession;

    public function __construct(PhpcrSession $phpcrSession)
    {
        $this->phpcrSession = $phpcrSession;
    }

    public function build(BuilderUnitInterface $builderUnit, BuilderContext $context)
    {
        $routeStack = new RouteStack;
        $builderUnit->pathAction($context);

        $exists = $this->phpcrSession->nodeExists($context->getPath()); 

        if ($exists) {
            $builderUnit->existsAction($context);
        } else {
            $builderUnit->notExistsAction($context);
        }
    }
}
