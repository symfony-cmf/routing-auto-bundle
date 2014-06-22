<?php

namespace Symfony\Cmf\Component\RoutingAuto\TokenProvider;

use Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\UrlContext;

class ContentLocaleProvider implements TokenProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function provideValue(UrlContext $urlContext, $options)
    {
        return $urlContext->getLocale();
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
    }
}
