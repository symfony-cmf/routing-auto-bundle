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
    protected $builderConfig;

    public function __construct(
        PathProviderInterface $pathProvider,
        PathActionInterface $existsAction,
        PathActionInterface $notExistsAction,
        array $builderConfig
    ) {
        $this->pathProvider = $pathProvider;
        $this->existsAction = $existsAction;
        $this->notExistsAction = $notExistsAction;
        $this->builderConfig = $builderConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function pathAction(RouteStack $routeStack)
    {
        $options = $this->pathProvider->getOptionsResolver()->resolve($this->builderConfig['provider']['options']);
        $this->pathProvider->providePath($routeStack, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function existsAction(RouteStack $routeStack)
    {
        $options = $this->existsAction->getOptionsResolver()->resolve($this->builderConfig['exists_action']['options']);
        $this->existsAction->execute($routeStack, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function notExistsAction(RouteStack $routeStack)
    {
        $options = $this->notExistsAction->getOptionsResolver()->resolve($this->builderConfig['not_exists_action']['options']);
        $this->notExistsAction->execute($routeStack, $options);
    }
}
