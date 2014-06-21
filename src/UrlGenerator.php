<?php

namespace Symfony\Cmf\Component\RoutingAuto\AutoRoute;

use Symfony\Cmf\Component\RoutingAuto\AutoRoute\Adapter\AdapterInterface;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class which handles URL generation and conflict resolution
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UrlGenerator implements UrlGeneratorInterface
{
    protected $driver;
    protected $metadataFactory;
    protected $serviceRegistry;

    /**
     * @param MetadataFactory   the metadata factory
     * @param AdapterInterface  the autoroute backend driver (odm ,orm, etc)
     * @param ServiceRegistry  the auto route service registry
     */
    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        AdapterInterface $driver,
        ServiceRegistry $serviceRegistry
    )
    {
        $this->metadataFactory = $metadataFactory;
        $this->driver = $driver;
        $this->serviceRegistry = $serviceRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function generateUrl(UrlContext $urlContext)
    {
        $realClassName = $this->driver->getRealClassName(get_class($urlContext->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        $tokenProviderConfigs = $metadata->getTokenProviders();

        $tokens = array();
        foreach ($tokenProviderConfigs as $name => $options) {
            $tokenProvider = $this->serviceRegistry->getTokenProvider($options['name']);

            // I can see the utility of making this a singleton, but it is a massive
            // code smell to have this in a base class and be also part of the interface
            $optionsResolver = new OptionsResolver();
            $tokenProvider->configureOptions($optionsResolver);

            $tokens['{' . $name . '}'] = $tokenProvider->provideValue($urlContext, $optionsResolver->resolve($options['options']));
        }

        $urlSchema = $metadata->getUrlSchema();
        $url = strtr($urlSchema, $tokens);

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveConflict(UrlContext $urlContext)
    {
        $realClassName = $this->driver->getRealClassName(get_class($urlContext->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        $conflictResolverConfig = $metadata->getConflictResolver();
        $conflictResolver = $this->serviceRegistry->getConflictResolver(
            $conflictResolverConfig['name'], 
            $conflictResolverConfig['options']
        );
        $url = $conflictResolver->resolveConflict($urlContext);

        return $url;
    }
}
