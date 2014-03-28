<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

class UrlContextStack
{
    protected $subjectObject;
    protected $urlContexts = array();

    /**
     * @param mixed $subjectObject Subject for URL generation
     */
    public function __construct($subjectObject)
    {
        $this->subjectObject = $subjectObject;
    }

    public function getSubjectObject() 
    {
        return $this->subjectObject;
    }

    /**
     * Create and add a URL context
     *
     * @param string $url    URL
     * @param string $locale Locale for given URL
     *
     * @return UrlContext
     */
    public function createUrlContext($locale)
    {
        $urlContext = new UrlContext(
            $this->getSubjectObject(),
            $locale
        );

        $this->urlContexts[] = $urlContext;

        return $urlContext;
    }

    public function getUrlContexts()
    {
        return $this->urlContexts;
    }

    public function containsRoute(RouteObjectInterface $route)
    {
        foreach ($this->urlContexts as $urlContext) {
            if ($route === $urlContext->getNewRoute()) {
                return true;
            }
        }

        return false;
    }
}
