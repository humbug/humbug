<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter\Phpunit;

use Humbug\Adapter\Phpunit\XmlConfiguration\Visitor;

class XmlConfiguration
{
    /**
     *
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var \DOMXPath
     */
    private $xpath;

    /**
     * @var \DOMElement
     */
    private $rootElement;

    /**
     * @var string
     */
    private $originalBootstrap;

    public function __construct(\DOMDocument $dom)
    {
        if (!$dom->documentElement) {
            throw new \LogicException('No document element present. Document should not be empty!');
        }

        $this->dom = $dom;
        $this->xpath = new \DOMXPath($this->dom);
        $this->rootElement = $this->dom->documentElement;
    }

    public function hasBootstrap()
    {
        return $this->rootElement->hasAttribute('bootstrap');
    }

    public function getBootstrap()
    {
        return $this->rootElement->getAttribute('bootstrap');
    }

    public function setBootstrap($bootstrap)
    {
        if (null === $this->originalBootstrap) {
            $actualBootstrap = $this->getBootstrap();
            $this->originalBootstrap = $actualBootstrap ?: false;
        }

        return $this->rootElement->setAttribute('bootstrap', $bootstrap);
    }

    public function hasOriginalBootstrap()
    {
        return ($this->getOriginalBootstrap() !== null);
    }

    public function getOriginalBootstrap()
    {
        if (null !== $this->originalBootstrap) {
            return $this->originalBootstrap ?: null;
        }

        return $this->getBootstrap() ?: null;
    }

    public function turnOffCacheTokens()
    {
        return $this->rootElement->setAttribute('cacheTokens', 'false');
    }

    public function cleanupLoggers()
    {
        $this->removeDocumentChildElementsByName('logging');
    }

    public function cleanupFilters()
    {
        $this->removeDocumentChildElementsByName('filter');
    }

    public function cleanupListeners()
    {
        $this->removeDocumentChildElementsByName('listeners');
    }

    private function removeDocumentChildElementsByName($name)
    {
        $nodes = $this->xpath->query('/phpunit/' . $name);

        foreach ($nodes as $node) {
            $this->rootElement->removeChild($node);
        }
    }

    public function addListener(Visitor $visitor)
    {
        $listenersList = $this->xpath->query('/phpunit/listeners');

        if ($listenersList->length) {
            $listeners = $listenersList->item(0);
        } else {
            $listeners = $this->dom->createElement('listeners');
            $this->rootElement->appendChild($listeners);
        }

        $listener = $this->dom->createElement('listener');
        $listeners->appendChild($listener);

        $visitor->visitElement($listener);
    }

    public function addLogger($type, $target)
    {
        $loggingList = $this->xpath->query('/phpunit/logging');

        if ($loggingList->length) {
            $logging = $loggingList->item(0);
        } else {
            $logging = $this->dom->createElement('logging');
            $this->rootElement->appendChild($logging);
        }

        $log = $this->dom->createElement('log');
        $logging->appendChild($log);

        $log->setAttribute('type', $type);
        $log->setAttribute('target', $target);
    }

    public function addWhiteListFilter(array $whiteListDirectories, array $excludeDirectories = [])
    {
        if (empty($whiteListDirectories)) {
            return;
        }

        $filter = $this->dom->createElement('filter');
        $this->rootElement->appendChild($filter);

        $whiteList = $this->dom->createElement('whitelist');
        $filter->appendChild($whiteList);

        foreach ($whiteListDirectories as $dirName) {
            $directory = $this->dom->createElement('directory', $dirName);
            $whiteList->appendChild($directory);
            $directory->setAttribute('suffix', '.php');
        }

        if (empty($excludeDirectories)) {
            return;
        }

        $exclude = $this->dom->createElement('exclude');
        $whiteList->appendChild($exclude);

        foreach ($excludeDirectories as $dirName) {
            $directory = $this->dom->createElement('directory', $dirName);
            $exclude->appendChild($directory);
        }
    }

    public function replacePathsToAbsolutePaths(Visitor $visitor)
    {
        $replacePaths = [
            '/phpunit/testsuites/testsuite/exclude',
            '//directory',
            '//file',
            '/phpunit/@bootstrap'
        ];

        $replaceQuery = implode('|', $replacePaths);

        $replaceNodes = $this->xpath->query($replaceQuery);

        foreach ($replaceNodes as $exclude) {
            $visitor->visitElement($exclude);
        }
    }

    public function generateXML()
    {
        return $this->dom->saveXML();
    }
}
