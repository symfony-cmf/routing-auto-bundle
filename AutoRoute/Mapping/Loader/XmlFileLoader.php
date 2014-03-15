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
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Config\Loader\FileLoader;


/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class XmlFileLoader extends FileLoader
{
    const NAMESPACE_URI = 'http://cmf.symfony.com/schema/routing_auto';
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

        // empty file
        if ('' === trim(file_get_contents($path))) {
            return;
        }
        
        $xml = XmlUtils::loadFile($path, __DIR__.static::SCHEMA_FILE);

        $metadatas = array();
        foreach ($xml->documentElement->getElementsByTagNameNS(self::NAMESPACE_URI, 'mapping') as $mappingNode) {
            $metadatas[] = $this->parseMappingNode($mappingNode, $path);
        }

        return $metadatas;
    }

    /**
     * @param \DOMElement $mappingNode
     * @param string      $path
     *
     * @return ClassMetadata
     */
    protected function parseMappingNode(\DOMElement $mappingNode, $path)
    {
        $className = $this->readAttribute($mappingNode, 'class', sprintf('in "%s"', $path));
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Configuration found for unknown class "%s" in "%s".', $className, $path));
        }
        $classMetadata = new ClassMetadata($className);

        try {
            $classMetadata->setUrlSchema(
                $this->readAttribute($mappingNode, 'url-schema', sprintf('for "%s" in "%s"', $className, $path))
            );

            $classMetadata->setExtendedClass(
                $this->readAttribute($mappingNode, 'extend', sprintf('for "%s" in "%s"', $className, $path))
            );
        } catch (\InvalidArgumentException $e) {
            // the extend and url-schema attributes may be omitted
        }

        $conflictResolverNodes = $mappingNode->getElementsByTagNameNS(self::NAMESPACE_URI, 'conflict-resolver');
        $resolversLength = $conflictResolverNodes->length;
        if (1 < $resolversLength) {
            throw new \InvalidArgumentException(sprintf('There can only be one conflict resolver per mapping, %d given for "%s" in ""%s', $resolversLength, $className, $path));
        } elseif (1 === $resolversLength) {
            $this->parseConflictResolverNode($conflictResolverNodes->item(0), $classMetadata, $path);
        }

        $tokenProviders = $mappingNode->getElementsByTagNameNS(self::NAMESPACE_URI, 'token-provider');
        // token providers can be omitted if the schema is constructed of 
        // global token providers only
        if (0 !== count($tokenProviders)) {
            foreach ($tokenProviders as $tokenNode) {
                $this->parseTokenProviderNode($tokenNode, $classMetadata, $path);
            }
        }

        return $classMetadata;
    }

    /**
     * @param \DOMElement   $tokenNode
     * @param ClassMetadata $classMetadata
     * @param string        $path
     */
    protected function parseTokenProviderNode(\DOMElement $tokenNode, ClassMetadata $classMetadata, $path)
    {
        $tokenName = $this->readAttribute($tokenNode, 'token', sprintf('in "%s" for "%s"', $path, $classMetadata->name));
        $providerName = $this->readAttribute($tokenNode, 'name', sprintf('in "%s" for "%s"', $path, $classMetadata->name));
        $providerOptions = $this->parseOptionNode($tokenNode->getElementsByTagNameNS(self::NAMESPACE_URI, 'option'), $path);

        $classMetadata->addTokenProvider($tokenName, array('name' => $providerName, 'options' => $providerOptions));
    }

    /**
     * @param \DOMElement   $tokenNode
     * @param ClassMetadata $classMetadata
     * @param string        $path
     */
    protected function parseConflictResolverNode(\DOMElement $node, ClassMetadata $classMetadata, $path)
    {
        $name = $this->readAttribute($node, 'name', sprintf('in "%s" for "%s"', $path, $classMetadata->name));
        $options = $this->parseOptionNode($node->getElementsByTagNameNS(self::NAMESPACE_URI, 'option'), $path);

        $classMetadata->setConflictResolver(array('name' => $name, 'options' => $options));
    }

    protected function parseOptionNode(\DOMNodeList $nodes, $path)
    {
        $options = array();
        foreach ($nodes as $node) {
            $options[$this->readAttribute($node, 'name', sprintf('in "%s"', $path))] = XmlUtils::phpize($node->nodeValue);
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'xml' === $type);
    }

    protected function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new YamlParser();
        }

        return $this->parser;
    }

    private function readAttribute(\DOMElement $node, $name, $location)
    {
        if (!$node->hasAttribute($name) || '' === $value = $node->getAttribute($name)) {
            throw new \InvalidArgumentException(sprintf('The <%s> element %s must have a %s attribute.', $node->tagName, $location, $name));
        }

        return $value;
    }
}
