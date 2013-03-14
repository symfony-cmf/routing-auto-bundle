<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\RouteMaker;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\RouteMakerInterface;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Document\AutoRoute;

/**
 * This class will make the Route classes using
 * Generic documents using.
 *
 * @todo: Make this use PHPCR\Util\NodeHelper:makePath
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class GenericMaker implements RouteMakerInterface
{
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function makeRoutes(BuilderContext $context)
    {
        $paths = $context->getPathStack();
        $fullPath = implode('/', $paths);
        $path = '';

        $components = preg_split('#/#', $fullPath, null, PREG_SPLIT_NO_EMPTY);

        // @todo: this is dumb but safe. We always check the FULL path (i.e. look-up each component). 
        //        An optimization would be to check to see if the route at the given path is already 
        //        in the stack before doing a lookup. Also
        //
        //         - Multi-lookup as in: 
        //           https://github.com/symfony-cmf/RoutingExtraBundle/blob/master/Document/RouteProvider.php#L100
        //         - This applies to the entire stack, but the previous builder should have
        //           already resolved part of the stack, we should only process stuff introduced in
        //           the context of this builder. I.e. there should be a sub-context for each builder request.
        
        foreach ($components as $i => $component) {
            $path .= '/'.$component;

            $doc = $this->dm->find(null, $path);

            if (null === $doc) {
                $parent = $context->getLastRoute();

                if (null === $parent) {
                    $parent = $this->dm->find(null, '/');
                }

                // @todo: This should not be in this class (this class should only be concerned with
                //        "filling the holes" in the path components leading up to the content object)
                if ($context->isLastBuilder() && ($i == count($components) - 1)) {
                    // If this is the last builder and this is the last component
                    // create the actual content route.
                    $doc = new AutoRoute;
                    $doc->setName($component);
                    $doc->setRouteContent($context->getObject());
                    $doc->setParent($parent);
                } else {
                    // otherwise create a generic document
                    $doc = new Generic;
                    $doc->setNodename($component);
                    $doc->setParent($parent);
                    var_dump($parent);
                }
            }

            $context->addRoute($doc);
        }
    }
}
