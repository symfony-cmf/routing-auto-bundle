<?php

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSet('default');

        if ($this->environment === 'doctrine_orm') {
            $this->requireBundleSet('doctrine_orm');
        } else {
            $this->requireBundleSet('phpcr_odm');
        }

        $this->addBundles(array(
            new \Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new \Symfony\Cmf\Bundle\RoutingAutoBundle\CmfRoutingAutoBundle(),

            new \Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Bundle\TestBundle\TestBundle(),
        ));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $env = $this->environment;

        $loader->import(CMF_TEST_CONFIG_DIR . '/default.php');
        $loader->import(CMF_TEST_CONFIG_DIR . '/doctrine_orm.php');

        $loader->import(__DIR__.'/config/'.$env.'.yml');
    }

    protected function buildContainer()
    {
        $container = parent::buildContainer();
        $container->setParameter('cmf_testing.bundle_fqn', 'Symfony\Cmf\Bundle\RoutingAutoBundle');

        return $container;
    }
}
