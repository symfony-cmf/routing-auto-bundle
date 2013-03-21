<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Doctrine\ODM\PHPCR\DocumentManager;

/**
 * This class is responsible for building and closing
 * a RouteStack from a given RouteStackBuilderUnit.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Builder
{
    protected $dm;

    public function __construct(DocumentManager $dm) 
    {
        $this->dm = $dm;
    }

    public function build(RouteStack $routeStack, BuilderUnitInterface $rsbu)
    {
        $rsbu->pathAction($routeStack);
        $fullPath = $routeStack->getFullPath();
        $absPath = '/'.$fullPath;

        $existingRoute = $this->dm->find(null, $absPath); 

        if ($existingRoute) {
            $rsbu->existsAction($routeStack);
        } else {
            $rsbu->notExistsAction($routeStack);
        }

        // hmm ... this seems wierd. Needs some refactoring.
        $routeStack->close();
        $routeStack->getContext()->commitRouteStack();
    }
}
