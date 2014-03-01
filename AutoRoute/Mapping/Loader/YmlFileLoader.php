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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MappingData;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\TokenProvider;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Config\Loader\FileLoader;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class YmlFileLoader extends FileLoader
{
    /** @var null|YamlParser */
    private $parser;

    public function __construct()
    {
    }

    /**
     * Loads a Yaml File.
     *
     * @param string      $path A Yaml file path
     * @param string|null $type
     *
     * @return MappingData[]
     *
     * @throws \InvalidArgumentException When the $file cannot be parsed
     */
    public function load($path, $type = null)
    {
        //$path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }
        
        $config = $this->getParser()->parse(file_get_contents($path));

        // empty file
        if (empty($config)) {
            return;
        }

        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        $mappings = array();
        foreach ($config as $className => $mapping) {
            if (!class_exists($className)) {
                throw new \InvalidArgumentException(sprintf('Configuration found for unknown class "%s" in "%s".', $className, $path));
            }
            $data = new MappingData($className);

            if (!isset($mapping['url_schema'])) {
                throw new \InvalidArgumentException(sprintf('No URL schema specified for "%s" in "%s".', $className, $path));
            }
            $data->setUrlSchema($mapping['url_schema']);

            // path units can be omitted if the schema is constructed of 
            // global path units only
            if (isset($mapping['path_units'])) {
                foreach ($mapping['path_units'] as $unitName => $unit) {
                    $data->addTokenProvider($this->parseTokenProvider($unitName, $unit, $className, $path));
                }
            }

            // add MappingData to registered mappings in the end, to ensure no 
            // incomplete mappings are registered.
            $mappings[] = $data;
        }

        return $mappings;
    }

    /**
     * @param string $unitName
     * @param array  $unit
     * @param string $className
     * @param string $path
     *
     * @return TokenProvider
     */
    protected function parseTokenProvider($unitName, $unit, $className, $path)
    {
        $tokenProvider = new TokenProvider($unitName);

        foreach (array(
            'provider' => 'setProvider',
            'exists_action' => 'setExistsAction',
            'not_exists_action' => 'setNotExistsAction',
        ) as $option => $method) {
            if (!isset($unit[$option])) {
                throw new \InvalidArgumentException(sprintf('"%s" must be specified for path unit "%s" for class "%s" in "%s".', $option, $unitName, $className, $path));
            }

            $service = $unit[$option];
            // provider: method
            if (is_string($service)) {
                $tokenProvider->$method($service);

                continue;
            }

            // provider: { name: method, options: { slugify: true } }
            if (isset($service['name'])) {
                // provider: { name: method }
                if (!isset($service['options'])) {
                    $service['options'] = array();
                }
                $tokenProvider->$method($service['name'], $service['option']);

                continue;
            }

            // provider: [method, { slugify: true }]
            if (2 === count($service) && isset($service[0]) && isset($service[1])) {
                $tokenProvider->$method($service[0], $service[1]);

                continue;
            }

            throw new \InvalidArgumentException(sprintf('Unknown builder service configuration for "%s" for class "%s" in "%s": %s', $unitName, $className, $path, json_encode($service)));
        }

        return $tokenProvider;
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
