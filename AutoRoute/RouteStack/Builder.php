<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

use PHPCR\SessionInterface as PhpcrSession;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * This class is responsible for building and closing
 * a RouteStack from a given RouteStackBuilderUnit.
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

    public function build(RouteStack $routeStack, BuilderUnitInterface $rsbu)
    {
        $rsbu->pathAction($routeStack);

        $exists = $this->phpcrSession->nodeExists('/'.$routeStack->getFullPath()); 

        if ($exists) {
            $rsbu->existsAction($routeStack);
        } else {
            $rsbu->notExistsAction($routeStack);
        }

        // hmm ... this seems wierd. Needs some refactoring.
        $routeStack->close();
        $routeStack->getContext()->commitRouteStack();
    }
}
