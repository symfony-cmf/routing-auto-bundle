<?php

namespace Symfony\Cmf\Component\RoutingAuto\AutoRoute;

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
