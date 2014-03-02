<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver\DriverInterface;

/**
 * Class which handles URL generation and conflict resolution
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UrlGenerator implements UrlGeneratorInterface
{
    protected $driver;
    protected $mappingFactory;
    protected $serviceRegistry;

    /**
     * @param MappingFactory   the metadata factory
     * @param DriverInterface  the autoroute backend driver (odm ,orm, etc)
     * @param ServiceRegistry  the auto route service registry
     */
    public function __construct(
        MappingFactory $mappingFactory,
        DriverInterface $driver,
        ServiceRegistry $serviceRegistry
    )
    {
        $this->factory = $factory;
        $this->driver = $driver;
        $this->serviceRegistry = $serviceRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function generateUrl($document)
    {
        $realClassName = $this->driver->getRealClassName($document);
        $mapping = $this->factory->getMappingForClass($realClassName);

        $tokenProviderConfigs = $mapping->getTokenProviderConfigs();

        $tokens = array();
        foreach ($tokenProviderConfigs as $name => $options) {
            $tokenProvider = $this->serviceRegistry->getTokenProvider($name);
            $tokens['%' . $name . '%'] = $tokenProvider->getValue($options, $document);
        }

        $urlSchema = $mapping->getUrlSchema();
        $url = strtr($urlSchema, $tokens);

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveConflict($document, $url)
    {
        $realClassName = $this->driver->getRealClassName($document);
        $mapping = $this->factory->getMappingForClass($realClassName);

        list ($name, $config) = $mapping->getConflictResolverConfig();
        $conflictResolver = $this->serviceRegistry->getConflictResolver($name, $config);
        $url = $conflictResolver->resolveConflict($url);

        return $url;
    }
}
