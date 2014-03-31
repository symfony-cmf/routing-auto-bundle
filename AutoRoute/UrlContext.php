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
    protected $subjectObject;
    protected $locale;
    protected $url;
    protected $autoRoute;

    public function __construct($subjectObject, $locale)
    {
        $this->subjectObject = $subjectObject;
        $this->locale = $locale;
    }

    public function getUrl() 
    {
        return $this->url;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    public function getSubjectObject() 
    {
        return $this->subjectObject;
    }

    public function getLocale() 
    {
        return $this->locale;
    }

    public function getAutoRoute() 
    {
        return $this->autoRoute;
    }
    
    public function setAutoRoute($autoRoute)
    {
        $this->autoRoute = $autoRoute;
    }
}
