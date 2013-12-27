<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver;

use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver\DriverInterface;

/**
 * Abstraction driver for PHPCR-ODM
 *
 * This class will eventually encapsulate all of the PHPCR-ODM
 * specific logic to enable support for multiple backends.
 */
class PhpcrOdmDriver implements DriverInterface
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function getLocales($document)
    {
        if ($this->dm->isDocumentTranslatable($document)) {
            return $this->dm->getLocalesFor($document);
        }

        return array();
    }

    public function translateObject($document, $locale)
    {
        $meta = $this->dm->getMetadataFactory()->getMetadataFor(get_class($document));
        $this->dm->findTranslation(get_class($document), $meta->getIdentifierValue($document), $locale);
        return $document;
    }
}
