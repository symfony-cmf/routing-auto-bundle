<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\CacheWarmer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

/**
 * Creates the required cache directory in order to make the FileCache working.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class AutoRouteCacheWarmer implements CacheWarmerInterface, CacheClearerInterface
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

    /**
     * {@inheritDoc}
     */
    public function clear($cacheDir)
    {
        if (is_dir($cacheDir.'/cmf/routing_auto')) {
            $this->filesystem->remove($cacheDir.'/cmf/routing_auto');
        }
    }
}
