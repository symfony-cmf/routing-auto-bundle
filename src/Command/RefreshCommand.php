<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Command;

use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter\AutoRouteRefreshCommandAdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\AutoRouteManager;
use Symfony\Cmf\Component\RoutingAuto\Mapping\MetadataFactory;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends ContainerAwareCommand
{
    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var AutoRouteManager
     */
    private $autoRouteManager;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var AutoRouteRefreshCommandAdapterInterface
     */
    private $adapter;

    /**
     * @var string
     */
    private $adapterName;

    public function configure()
    {
        $this
            ->setName('cmf:routing:auto:refresh')
            ->setDescription('Refresh auto-routeable documents')
            ->setHelp(<<<'HERE'
WARNING: Experimental!

This command iterates over all Documents that are mapped by the auto
routing system and re-applys the auto routing logic.

You can specify the "--verbose" option to output detail for each created
route.

Specify the "--dry-run" option to not write any changes to the database.

Use "--class" to only apply the changes to a single class - although beware this
may cause an error if you persist a class whose auto routing configuration
relies on the auto routing of another class.
HERE
        );

        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Do not write any change to the database.'
        );
        $this->addOption(
            'class',
            null,
            InputOption::VALUE_REQUIRED,
            'Only update the given class FQN'
        );
        $this->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The session to use for this command');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $container = $this->getContainer();
        $this->adapterName = $container->getParameter('cmf_routing_auto.adapter_name');
        if ('doctrine_orm' === $this->adapterName) {
            $this->manager = $container->get('doctrine')->getManager();
            $this->adapter = $container->get('cmf_routing_auto.adapter.orm');
        } else {
            $this->manager = $container->get('doctrine_phpcr')->getManager();
            $this->adapter = $container->get('cmf_routing_auto.adapter.phpcr_odm');
        }

        $this->metadataFactory = $container->get('cmf_routing_auto.metadata.factory');
        $this->autoRouteManager = $container->get('cmf_routing_auto.auto_route_manager');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        // TODO: check if it is phpcr check for session or if it is orm do not accept session
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $input->getOption('session');
        $dryRun = $input->getOption('dry-run');
        $class = $input->getOption('class');
        $verbose = $input->getOption('verbose');

        if ('doctrine_phpcr_odm' === $this->adapterName) {
            DoctrineCommandHelper::setApplicationPHPCRSession(
                $this->getApplication(),
                $session
            );
        }

        if ($class) {
            $mapping = [$class => $class];
        } else {
            $mapping = iterator_to_array($this->metadataFactory->getIterator());
        }

        foreach (array_keys($mapping) as $classFqn) {
            $output->writeln(sprintf('<info>Processing class: </info> %s', $classFqn));

            $result = $this->adapter->getAllContent($classFqn);

            foreach ($result as $autoRouteableDocument) {
                $id = $this->adapter->getIdentifier($autoRouteableDocument);

                $output->writeln('  <info>Refreshing: </info>'.$id);

                $uriContextCollection = new UriContextCollection($autoRouteableDocument);
                $this->autoRouteManager->buildUriContextCollection($uriContextCollection);

                foreach ($uriContextCollection->getUriContexts() as $uriContext) {
                    $autoRoute = $uriContext->getAutoRoute();
                    $this->manager->persist($autoRoute);

                    $autoRouteId = $this->adapter->getIdentifier($autoRoute);

                    if ($verbose) {
                        $output->writeln(sprintf(
                            '<comment>    - %sPersisting: </comment> %s <comment>%s</comment>',
                            $dryRun ? '(dry run) ' : '',
                            $autoRouteId,
                            '[...]'.substr(get_class($autoRoute), -10)
                        ));
                    }

                    if (true !== $dryRun) {
                        $this->manager->flush();
                    }
                }
            }
        }
    }
}
