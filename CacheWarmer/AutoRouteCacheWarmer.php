<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\CacheWarmer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Creates the required cache directory in order to make the FileCache working.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class AutoRouteCacheWarmer implements CacheWarmerInterface
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * This is not optional, the FileCache will fail if the directory does not 
     * exists.
     *
     * {@inheritDocs}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function warmUp($cacheDir)
    {
        $this->filesystem->mkdir($cacheDir.'/cmf/routing_auto');
    }
}
