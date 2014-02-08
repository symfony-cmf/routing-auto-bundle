<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

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
