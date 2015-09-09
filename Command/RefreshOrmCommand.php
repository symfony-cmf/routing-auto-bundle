<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;

class RefreshOrmCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('cmf:routing:orm:auto:refresh')
            ->setDescription('Refresh auto-routeable entities')
            ->setHelp(<<<HERE
WARNING: Experimental!

This command iterates over all Entities that are mapped by the auto
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
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $manager = $container->get('doctrine');
        $factory = $container->get('cmf_routing_auto.metadata.factory');
        $arm = $container->get('cmf_routing_auto.auto_route_manager');

        $em = $manager->getManager();
        $uow = $em->getUnitOfWork();

        $dryRun = $input->getOption('dry-run');
        $class = $input->getOption('class');
        $verbose = $input->getOption('verbose');

        if ($class) {
            $mapping = array($class => $class);
        } else {
            $mapping = iterator_to_array($factory->getIterator());
        }

        foreach (array_keys($mapping) as $classFqn) {
            $output->writeln(sprintf('<info>Processing class: </info> %s', $classFqn));

            $qb = $em->createQueryBuilder();
            $qb->select('a')
                ->from($classFqn, 'a');
            $q = $qb->getQuery();
            $result = $q->getResult();

            foreach ($result as $autoRouteableEntity) {
                $id = $uow->getSingleIdentifierValue($autoRouteableEntity);
                $output->writeln('  <info>Refreshing: </info>'.$id);

                $uriContextCollection = new UriContextCollection($autoRouteableEntity);
                $arm->buildUriContextCollection($uriContextCollection);

                foreach ($uriContextCollection->getUriContexts() as $uriContext) {
                    $autoRoute = $uriContext->getAutoRoute();
                    $em->persist($autoRoute);
                    $autoRouteId = $uow->getSingleIdentifierValue($autoRoute);
                    if ($verbose) {
                        $output->writeln(sprintf(
                            '<comment>    - %sPersisting: </comment> %s <comment>%s</comment>',
                            $dryRun ? '(dry run) ' : '',
                            $autoRouteId['id'],
                            '[...]'.substr(get_class($autoRoute), -10).' '.$autoRoute->getStaticPrefix()
                        ));
                    }

                    if (true !== $dryRun) {
                        $em->flush();
                    }
                }
            }
        }
    }
}
