<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use PHPCR\SessionInterface as PhpcrSession;

/**
 * Represents a route stack builder /unit/.
 *
 * The unit is a collection of classes required to
 * build and close a RouteStack.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteStackBuilderUnit implements RouteStackBuilderUnitInterface
{
    protected $pathProvider;
    protected $existsAction;
    protected $notExistsAction;

    public function __construct(
        PathProviderInterface $pathProvider, 
        PathActionInterface $existsAction, 
        PathActionInterface $notExistsAction
    ) {
        $this->pathProvider = $pathProvider;
        $this->existsAction = $existsAction;
        $this->notExistsAction = $notExistsAction;
    }

    /**
     * {@inheritDoc}
     */
    public function pathAction(RouteStack $routeStack, BuilderContext $context)
    {
        $this->pathProvider->providePath($routeStack, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function existsAction(RouteStack $routeStack, BuilderContext $context)
    {
        $this->existsAction->execute($routeStack, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function notExistsAction(RouteStack $routeStack, BuilderContext $context)
    {
        $this->notExistsAction->execute($routeStack, $context);
    }
}
