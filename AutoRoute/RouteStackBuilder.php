<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use PHPCR\SessionInterface as PhpcrSession;

/**
 * This class is responsible for building and closing
 * a RouteStack from a given RouteStackBuilderUnit.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteStackBuilder
{
    protected $phpcrSession;

    public function __construct(PhpcrSession $phpcrSession) 
    {
        $this->phpcrSession = $phpcrSession;
    }

    public function build(RouteStack $routeStack, RouteStackBuilderUnitInterface $rsbu, BuilderContext $context)
    {
        $rsbu->pathAction($routeStack, $context);

        $exists = $this->phpcrSession->nodeExists($context->getStagedPath()); 

        if ($exists) {
            $rsbu->existsAction($routeStack, $context);
        } else {
            $rsbu->notExistsAction($routeStack, $context);
        }

        $routeStack->close();
    }
}
