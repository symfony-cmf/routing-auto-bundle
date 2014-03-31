<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\Loader;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Config\Loader\FileLoader;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class YmlFileLoader extends FileLoader
{
    /** @var null|YamlParser */
    private $parser;

    /**
     * Loads a Yaml File.
     *
     * @param string      $path A Yaml file path
     * @param string|null $type
     *
     * @return ClassMetadata[]
     *
     * @throws \InvalidArgumentException When the $file cannot be parsed
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        $config = $this->getParser()->parse(file_get_contents($path));

        // empty file
        if (empty($config)) {
            return array();
        }

        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        $metadatas = array();
        foreach ($config as $className => $mappingNode) {
            $metadatas[] = $this->parseMappingNode($className, $mappingNode, $path);
        }

        return $metadatas;
    }

    /**
     * @param string $className
     * @param array  $mappingNode
     * @param string $path
     */
    protected function parseMappingNode($className, $mappingNode, $path)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Configuration found for unknown class "%s" in "%s".', $className, $path));
        }
        $classMetadata = new ClassMetadata($className);

        if (isset($mappingNode['url_schema'])) {
            $classMetadata->setUrlSchema($mappingNode['url_schema']);
        }

        if (isset($mappingNode['conflict_resolver'])) {
            $classMetadata->setConflictResolver($this->parseServiceConfig($mappingNode['conflict_resolver'], $className, $path));
        }

        if (isset($mappingNode['extend'])) {
            $classMetadata->setExtendedClass($mappingNode['extend']);
        }

        // token providers can be omitted if the schema is constructed of
        // inherited token providers only
        if (isset($mappingNode['token_providers'])) {
            foreach ($mappingNode['token_providers'] as $tokenName => $provider) {
                $classMetadata->addTokenProvider($tokenName, $this->parseServiceConfig($provider, $className, $path));
            }
        }

        return $classMetadata;
    }

    /**
     * @param mixed  $service
     * @param string $className
     * @param string $path
     *
     * @return array
     */
    protected function parseServiceConfig($service, $className, $path)
    {
        $name = '';
        $options = array();

        if (is_string($service)) {
            // provider: method
            $name = $service;
        } elseif (isset($service['name'])) {
            if (isset($service['options'])) {
                // provider: { name: method, options: { slugify: true } }
                $options = $service['options'];
            }

            // provider: { name: method }
            $name = $service['name'];
        } elseif (2 === count($service) && isset($service[0]) && isset($service[1])) {
            // provider: [method, { slugify: true }]
            $name = $service[0];
            $options = $service[1];
        } else {
            throw new \InvalidArgumentException(sprintf('Unknown builder service configuration for "%s" for class "%s" in "%s": %s', $name, $className, $path, json_encode($service)));
        }

        return array('name' => $name, 'options' => $options);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'yaml' === $type);
    }

    protected function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new YamlParser();
        }

        return $this->parser;
    }
}
