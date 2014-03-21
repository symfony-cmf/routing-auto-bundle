<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class which represents a URL and its associated locale
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UrlContext
{
    protected $object;
    protected $locale;

    public function __construct($object, $locale)
    {
        $this->object = $object;
        $this->locale = $locale;
    }

    public function getObject() 
    {
        return $this->object;
    }

    public function getLocale() 
    {
        return $this->locale;
    }
}
