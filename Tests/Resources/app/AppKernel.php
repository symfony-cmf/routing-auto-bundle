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

            new \Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Bundle\TestBundle\TestBundle(),
        ));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->import(CMF_TEST_CONFIG_DIR.'/default.php');

        $env = $this->environment;

        // the "testing "component sets the environment to "phpcr"
        if ($env === 'phpcr') {
            $env = 'doctrine_phpcr_odm';
        }

        if ($env === 'doctrine_phpcr_odm') {
            $loader->import(CMF_TEST_CONFIG_DIR.'/phpcr_odm.php');
        }

        $loader->import(__DIR__.'/config/'.$env.'.yml');
    }

    protected function buildContainer()
    {
        $container = parent::buildContainer();
        $container->setParameter('cmf_testing.bundle_fqn', 'Symfony\Cmf\Bundle\RoutingAutoBundle');

        return $container;
    }
}
