<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\Bundle\PHPCRBundle\Command\DoctrineCommandHelper;

class RefreshCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('cmf:routing:auto:refresh')
            ->setDescription('Refresh auto-routeable documents')
            ->setHelp(<<<HERE
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

        $this->addOption('dry-run', null, InputOption::VALUE_NONE,
            'Do not write any change to the database.'
        );
        $this->addOption('class', null, InputOption::VALUE_REQUIRED,
            'Only update the given class FQN'
        );
        $this->addOption('session', null, InputOption::VALUE_OPTIONAL, 'The session to use for this command');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $dm = $container->get('doctrine_phpcr.odm.default_document_manager');
        $factory = $container->get('cmf_routing_auto.factory');
        $arm = $container->get('cmf_routing_auto.auto_route_manager');
        $uow = $dm->getUnitOfWork();

        $session = $input->getOption('session');
        $dryRun = $input->getOption('dry-run');
        $class = $input->getOption('class');
        $verbose = $input->getOption('verbose');

        DoctrineCommandHelper::setApplicationPHPCRSession(
            $this->getApplication(),
            $session
        );

        if ($class) {
            $mapping = array($class => $class);
        } else {
            $mapping = $factory->getMappings();
        }

        foreach (array_keys($mapping) as $classFqn) {

            $output->writeln(sprintf('<info>Processing class: </info> %s', $classFqn));

            $qb = $dm->createQueryBuilder();
            $qb->from()->document($classFqn, 'a');
            $q = $qb->getQuery();
            $result = $q->getResult();

            foreach ($result as $autoRouteableDocument) {
                $id = $uow->getDocumentId($autoRouteableDocument);
                $output->writeln('  <info>Refreshing: </info>'.$id);
                $contexts = $arm->updateAutoRouteForDocument($autoRouteableDocument);

                foreach ($contexts as $context) {
                    foreach ($context->getRoutes() as $route) {
                        $dm->persist($route);
                        $routeId = $uow->getDocumentId($route);

                        if ($verbose) {
                            $output->writeln(sprintf(
                                '<comment>    - %sPersisting: </comment> %s <comment>%s</comment>',
                                $dryRun ? '(dry run) ' : '',
                                $routeId,
                                '[...]'.substr(get_class($route), -10)
                            ));
                        }

                        if (true !== $dryRun) {
                            $dm->flush();
                        }
                    }
                }
            }
        }
    }
}
