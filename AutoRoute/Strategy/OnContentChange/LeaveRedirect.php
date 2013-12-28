<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\AutoRouteChanged;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\AutoRouteChangedInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Doctrine\ODM\PHPCR\DocumentManager;
use PHPCR\Util\PathHelper;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRedirectRoute;

class LeaveRedirect implements AutoRouteChangedInterface
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function execute(BuilderContext $context)
    {
        $originalPath = $context->getOriginalAutoRoutePath();
        $parentPath = PathHelper::getParentPath($originalPath);
        $name = PathHelper::getNodeName($originalPath);
        $parent = $this->dm->find(null, $parentPath);

        $redirectRoute = new AutoRedirectRoute;
        $redirectRoute->setParent($parent);
        $redirectRoute->setName($name);
        $redirectRoute->setRouteTarget($context->getTopRoute());

        $context->addExtraDocument($redirectRoute);
    }
}
