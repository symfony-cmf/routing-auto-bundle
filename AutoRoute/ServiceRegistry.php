<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProviderInterface;

class ServiceRegistry
{
    protected $tokenProviders = array();
    protected $conflictResolvers = array();

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

    public function registerTokenProvider($name, TokenProviderInterface $provider)
    {
        $this->tokenProviders[$name] = $provider;
    }

    public function registerConflictResolver($name, ConflictResolverInterface $conflictResolver)
    {
        $this->conflictResolvers[$name] = $conflictResolver;
    }
}
