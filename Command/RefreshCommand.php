<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('cmf:routing:auto:refresh')
            ->setDescription('Refresh all auto routes')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $dm = $container->get('doctrine_phpcr.odm.default_document_manager');
        $factory = $container->get('symfony_cmf_routing_auto.factory');
        $arm = $container->get('symfony_cmf_routing_auto.auto_route_manager');
        $uow = $dm->getUnitOfWork();

        $mapping = $factory->getMappings();

        foreach (array_keys($mapping) as $classFqn) {

            $qb = $dm->createQueryBuilder();
            $qb->from($classFqn);
            $q = $qb->getQuery();
            $result = $q->getResult();

            foreach ($result as $autoRouteableDocument) {
                $id = $uow->getDocumentId($autoRouteableDocument);
                $output->writeln('<info>Refreshing: </info>'.$id);
                $context = $arm->updateAutoRouteForDocument($autoRouteableDocument);
                foreach ($context->getRoutes() as $route) {
                    $dm->persist($route);
                    $routeId = $uow->getDocumentId($route);
                    $output->writeln(sprintf(
                        '<comment>  - Persisting: </comment> %s <comment>%s</comment>',
                        $routeId,
                        '[...]'.substr(get_class($route), -10)
                    ));
                    $dm->flush();
                }
            }
        }
    }
}
