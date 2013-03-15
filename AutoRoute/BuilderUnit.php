<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
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

    public function pathAction(BuilderContext $context)
    {
        $this->pathProvider->providePath($context);
    }

    public function existsAction(BuilderContext $context)
    {
        $this->existsAction->execute($context);
    }

    public function notExistsAction(BuilderContext $context)
    {
        $this->notExistsAction->execute($context);
    }
}
