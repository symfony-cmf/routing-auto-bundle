<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

class ServiceRegistry
{
    protected $tokenProviders = array();
    protected $conflictResolvers = array();
    protected $defunctRouteHandlers = array();

    /**
     * Return the named token provider.
     *
     * @throws \InvalidArgumentException if the named token provider does not exist.
     *
     * @return TokenProviderInterface
     */
    public function getTokenProvider($name)
    {
        if (!isset($this->tokenProviders[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Token provider with name "%s" has not been registered',
                $name
            ));
        }

        return $this->tokenProviders[$name];
    }

    /**
     * Return the named conflict resolver.
     *
     * @throws \InvalidArgumentException if the named token provider does not exist.
     *
     * @return ConflictResolverInterface
     */
    public function getConflictResolver($name)
    {
        if (!isset($this->conflictResolvers[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Conflict resolver with name "%s" has not been registered',
                $name
            ));
        }

        return $this->conflictResolvers[$name];
    }

    /**
     * Return the named conflict resolver.
     *
     * @throws \InvalidArgumentException if the named token provider does not exist.
     *
     * @return ConflictResolverInterface
     */
    public function getDefunctRouteHandler($name)
    {
        if (!isset($this->defunctRouteHandlers[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Defunct route handler with name "%s" has not been registered',
                $name
            ));
        }

        return $this->defunctRouteHandlers[$name];
    }

    /**
     * Register the given token provider under the given name
     *
     * @param string                 $name
     * @param TokenProviderInterface $provider
     */
    public function registerTokenProvider($name, TokenProviderInterface $provider)
    {
        $this->tokenProviders[$name] = $provider;
    }

    /**
     * Register the given conflict resolver  under the given name
     *
     * @param string                    $name
     * @param ConflictResolverInterface $conflictResolver
     */
    public function registerConflictResolver($name, ConflictResolverInterface $conflictResolver)
    {
        $this->conflictResolvers[$name] = $conflictResolver;
    }

    /**
     * Register the given defunct route handler under the given name
     *
     * @param string                       $name
     * @param DefunctRouteHandlerInterface $defunctRouteHandler
     */
    public function registerDefunctRouteHandler($name, DefunctRouteHandlerInterface $defunctRouteHandler)
    {
        $this->defunctRouteHandlers[$name] = $defunctRouteHandler;
    }
}
