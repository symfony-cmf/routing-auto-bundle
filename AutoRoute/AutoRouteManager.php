<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

use Doctrine\ODM\PHPCR\DocumentManager;
use Metadata\MetadataFactoryInterface;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Document\AutoRoute;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use PHPCR\Util\NodeHelper;
use Doctrine\Common\Util\ClassUtils;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $bucf;

    public function __construct(BuilderUnitChainFactory $bucf)
    {
        $this->bucf = $bucf;
    }

    /**
     * Create or update the automatically generated route for
     * the given document.
     *
     * When this is finished it will support multiple locales.
     *
     * @param object Mapped document for which to generate the AutoRoute
     *
     * @return AutoRoute
     */
    public function updateAutoRouteForDocument($document)
    {
        $context = new BuilderContext;
        $context->setObject($document);

        $builderUnitChain = $this->bucf->getChain(ClassUtils::getClass($document));
        $builderUnitChain->executeChain($context);

        return $context;
    }

    /**
     * Remove all auto routes associated with the given document.
     *
     * @param object $document Mapped document
     *
     * @todo: Test me
     *
     * @return array Array of removed routes
     */
    public function removeAutoRoutesForDocument($document)
    {
        throw new \Exception('Implement me??');
    }

    /**
     * Return true if the given document is mapped with AutoRoute
     *
     * @param object $document Document
     *
     * @return boolean
     */
    public function isAutoRouteable($document)
    {
        return $this->bucf->hasMapping(ClassUtils::getClass($document));
    }
}
