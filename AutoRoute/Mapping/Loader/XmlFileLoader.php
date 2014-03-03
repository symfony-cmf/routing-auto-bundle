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
use Symfony\Component\Config\Loader\FileLoader;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class XmlFileLoader extends FileLoader
{
    const NAMESPACE_URI = 'http://cmf.symfony.com/schema/dic/routing-auto-mapping';
    const SCHEMA_FILE = '/schema/auto-routing/auto-routing-1.0.xsd';

    /**
     * Loads an XML File.
     *
     * @param string      $path An XML file path
     * @param string|null $type
     *
     * @return MappingData[]
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
        
        $xml = XmlUtils::loadFile(file_get_contents($path), __DIR__.static::SCHEMA_FILE);

        // empty file
        $mappings = array();
        foreach ($xml->documentElement->childNodes as $mappingNode) {
            if (!$node instanceof \DOMElement || self::NAMESPACE_URI !== $mappingNode->namespaceURI) {
                continue;
            }

            if (!$mappingNode->hasAttribute('class') || '' === $className = $mappingNode->getAttribute('class')) {
                throw new \InvalidArgumentException(sprintf('The <mapping> element in "%s" must have a class attribute', $path));
            }

            if (!class_exists($className)) {
                throw new \InvalidArgumentException(sprintf('Configuration found for unknown class "%s" in "%s".', $className, $path));
            }
            $data = new MappingData($className);

            if (!$mapping->hasAttribute('url-schema') || '' === $urlSchema = $mappingNode->getAttribute('url-schema')) {
                throw new \InvalidArgumentException(sprintf('No URL schema specified for "%s" in "%s".', $className, $path));
            }
            $data->setUrlSchema($urlSchema);

            $tokenProviders = $mappingNode->getElementsByTagNameNS(self::NAMESPACE_URI, 'token-provider');
            // token providers can be omitted if the schema is constructed of 
            // global token providers only
            if (0 !== count($tokenProviders)) {
                foreach ($tokenProviders as $tokenNode) {
                    $data->addTokenProvider($this->parseTokenProvider($tokenNode, $className, $path));
                }
            }

            // add MappingData to registered mappings in the end, to ensure no 
            // incomplete mappings are registered.
            $mappings[] = $data;
        }

        return $mappings;
    }

    /**
     * @param DOMElement $tokenNode
     * @param string     $className
     * @param string     $path
     *
     * @return TokenProvider
     */
    protected function parseTokenProvider($tokenNode, $className, $path)
    {
        if (!$tokenNode->hasAttribute('name') || '' === $tokenName = $tokenNode->getAttribute('name')) {
            throw new \InvalidArgumentException(sprintf('The <token> element in "%s" must have a name attribute.', $path));
        }
        $tokenProvider = new TokenProvider($tokenName);

        foreach (array(
            'provider' => 'setProvider',
            'exists-action' => 'setExistsAction',
            'not-exists-action' => 'setNotExistsAction',
        ) as $nodeName => $method) {
            $serviceNode = $tokenNode->getElementsByTagNameNS(static::NAMESPACE_URI, $nodeName);
            if (1 !== count($serviceNode)) {
                throw new \InvalidArgumentException(sprintf('Token provider "%s" for class "%s" in "%s" must one <%s> element.', $tokenName, $className, $path, $nodeName));
            }

            if (!$serviceNode->hasAttribute('name') || '' === $serviceName = $serviceNode->getAttribute('name')) {
                throw new \InvalidArgumentException(sprintf('Element <%s> for token provider "%s" for class "%s" in "%s" must have a name attribute.', $nodeName, $tokenName, $className, $path));
            }

            $optionNodes = $serviceNode->getElementsByTagNameNS(static::NAMESPACE_URI, 'option');
            $normalizedOptions = array();
            foreach ($optionNodes as $optionNode) {
                if (!$optionNode->hasAttribute('name') || '' === $optionName = $optionNode->getAttribute('name')) {
                    throw new \InvalidArgumentException(sprintf('The <option> element for "%s" for token provider "%s" for class "%s" in "%s" must have a name attribute.', $serviceName, $tokenName, $className, $path));
                }

                $optionValue = $optionNode->nodeValue;
                if ('' === trim($optionValue)) {
                    throw new \InvalidArgumentException(sprintf('The <option> element for "%s" for token provider "%s" for class "%s" in "%s" must have a value.', $serviceName, $tokenName, $className, $path));
                }

                $normalizedOptions[$optionName] = $optionValue;
            }

            $tokenProvider->$method($serviceName, $normalizedOptions);
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
