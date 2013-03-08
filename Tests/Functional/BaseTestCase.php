<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\Functional;

require __DIR__.'/app/AppKernel.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{
    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    protected $dm;

    static protected function createKernel(array $options = array())
    {
        return new AppKernel(
            isset($options['config']) ? $options['config'] : 'default.yml'
        );
    }

    public function getContainer()
    {
        return self::$kernel->getContainer();
    }

    public function setUp(array $options = array(), $routebase = null)
    {
        self::$kernel = self::createKernel($options);
        self::$kernel->init();
        self::$kernel->boot();

        $this->dm = $this->getContainer()->get('doctrine_phpcr.odm.document_manager');

        if (null == $routebase) {
            return;
        }

        $session = $this->getContainer()->get('doctrine_phpcr.session');

        if ($session->nodeExists("/test/$routebase")) {
            $session->getNode("/test/$routebase")->remove();
        }

        if (! $session->nodeExists('/test')) {
            $session->getRootNode()->addNode('test', 'nt:unstructured');
        }

        $session->save();
    }
}

