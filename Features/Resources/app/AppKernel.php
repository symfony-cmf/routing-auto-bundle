<?php

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSets(array(
            'default', 'phpcr_odm',
        ));

        $this->addBundles(array(
            new \Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new \Symfony\Cmf\Bundle\RoutingAutoBundle\CmfRoutingAutoBundle(),
        ));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->import(CMF_TEST_CONFIG_DIR.'/default.php');
        $loader->import(CMF_TEST_CONFIG_DIR.'/phpcr_odm.php');
    }
}
