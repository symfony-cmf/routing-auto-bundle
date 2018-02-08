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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Cmf\Component\RoutingAuto\AutoRouteManager;
use Symfony\Cmf\Component\RoutingAuto\Mapping\MetadataFactory;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RefreshOrmCommand.
 *
 * @author WAM Team <develop@wearemarketing.com>
 */
class RefreshOrmCommand extends ContainerAwareCommand
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function configure()
    {
        $this
            ->setName('cmf:routing:orm:auto:refresh')
            ->setDescription('Refresh auto-routeable entities')
            ->setHelp(<<<'HERE'
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
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $container = $this->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->metadataFactory = $container->get('cmf_routing_auto.metadata.factory');
        $this->autoRouteManager = $container->get('cmf_routing_auto.auto_route_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->getOption('dry-run');
        $class = $input->getOption('class');
        $verbose = $input->getOption('verbose');

        $entities = [];

        if ($class) {
            $mapping = [$class => $class];
        } else {
            $mapping = iterator_to_array($this->metadataFactory->getIterator());
        }

        $array_keys = array_keys($mapping);
        foreach ($array_keys as $classFqn) {
            $currentClass = new \ReflectionClass($classFqn);
            if ($currentClass->isAbstract()) {
                $this->processRoutesForAnAbstractAutoRouteClass($output, $classFqn, $entities, $currentClass, $verbose, $dryRun);
            } else {
                $this->processRoutes($output, $classFqn, $verbose, $dryRun);
            }
        }
    }

    /**
     * @param \ReflectionClass $class
     * @param \ReflectionClass $parentClassName
     */
    protected function isParentClass(\ReflectionClass $class, \ReflectionClass $parentClassName)
    {
        $parentClass = $class->getParentClass();

        if ((false === $parentClass) || (null === $parentClass)) {
            return false;
        }

        if (0 === strcmp($parentClass->getName(), $parentClassName->getName())) {
            return true;
        }

        return $this->isParentClass($parentClass, $parentClassName);
    }

    /**
     * @param OutputInterface $output
     * @param $classFqn
     * @param $verbose
     * @param $dryRun
     */
    protected function processRoutes(OutputInterface $output, $classFqn, $verbose, $dryRun)
    {
        $output->writeln(sprintf('<info>Processing class: </info> %s', $classFqn));

        $result = $this->getAutoRoutes($classFqn);

        foreach ($result as $autoRouteableEntity) {
            $id = $this->getId($autoRouteableEntity);
            $output->writeln('  <info>Refreshing: </info>'.$id);

            $uriContextCollection = new UriContextCollection($autoRouteableEntity);
            $this->autoRouteManager->buildUriContextCollection($uriContextCollection);

            foreach ($uriContextCollection->getUriContexts() as $uriContext) {
                $autoRoute = $uriContext->getAutoRoute();
                $this->entityManager->persist($autoRoute);

                $autoRouteId = $this->getId($autoRoute);

                if ($verbose) {
                    $output->writeln(
                        sprintf(
                            '<comment>    - %sPersisting: </comment> %s <comment>%s</comment>',
                            $dryRun ? '(dry run) ' : '',
                            $autoRouteId['id'],
                            '[...]'.substr(get_class($autoRoute), -10).' '.$autoRoute->getStaticPrefix()
                        )
                    );
                }

                if (true !== $dryRun) {
                    $this->entityManager->flush();
                }
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param $classFqn
     * @param $entities
     * @param $currentClass
     * @param $verbose
     * @param $dryRun
     *
     * @throws \ReflectionException
     */
    private function processRoutesForAnAbstractAutoRouteClass(OutputInterface $output, $classFqn, $entities, $currentClass, $verbose, $dryRun)
    {
        $meta = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            if (0 === strcmp($m->getName(), $classFqn)) {
                continue;
            }

            $entities[] = $m->getName();
        }

        foreach ($entities as $entityFqn) {
            $currentEntity = new \ReflectionClass($entityFqn);

            if ($this->isParentClass($currentEntity, $currentClass) && !$currentEntity->isAbstract()) {
                $this->processRoutes($output, $entityFqn, $verbose, $dryRun);
            }
        }
    }

    /**
     * @param $classFqn
     * @return array
     */
    protected function getAutoRoutes($classFqn): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from($classFqn, 'a');
        $q = $qb->getQuery();
        $result = $q->getResult();

        return $result;
    }

    /**
     * @param $autoRouteableEntity
     * @return array
     */
    protected function getId($autoRouteableEntity)
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $id = $unitOfWork->getSingleIdentifierValue($autoRouteableEntity);

        return $id;
    }
}
