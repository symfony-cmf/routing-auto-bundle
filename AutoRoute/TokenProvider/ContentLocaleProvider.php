<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext;

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
