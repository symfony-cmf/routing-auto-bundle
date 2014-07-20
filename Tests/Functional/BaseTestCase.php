<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase as TestingBaseTestCase;

class BaseTestCase extends TestingBaseTestCase
{
    public function setUp(array $options = array(), $routebase = null)
    {
        $session = $this->getContainer()->get('doctrine_phpcr.session');

        if ($session->nodeExists('/test')) {
            $session->getNode('/test')->remove();
        }

        if (!$session->nodeExists('/test')) {
            $session->getRootNode()->addNode('test', 'nt:unstructured');
            $session->getNode('/test')->addNode('auto-route');
        }

        $session->save();
    }

    public function getApplication()
    {
        $application = new Application(self::$kernel);

        return $application;
    }

    public function getDm()
    {
        return $this->db('PHPCR')->getOm();
    }
}
