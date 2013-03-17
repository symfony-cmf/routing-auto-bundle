<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

use PHPCR\SessionInterface as PhpcrSession;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * Represents a route stack builder /unit/.
 *
 * The unit is a collection of classes required to
 * build and close a RouteStack.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderUnit implements BuilderUnitInterface
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
    public function pathAction(RouteStack $routeStack)
    {
        $this->pathProvider->providePath($routeStack);
    }

    /**
     * {@inheritDoc}
     */
    public function existsAction(RouteStack $routeStack)
    {
        $this->existsAction->execute($routeStack);
    }

    /**
     * {@inheritDoc}
     */
    public function notExistsAction(RouteStack $routeStack)
    {
        $this->notExistsAction->execute($routeStack);
    }
}
