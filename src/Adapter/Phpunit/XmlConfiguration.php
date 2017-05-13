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
use Humbug\Exception\LogicException;

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
     * @var string|boolean
     */
    private $originalBootstrap;

    public function __construct(\DOMDocument $dom)
    {
        if (!$dom->documentElement) {
            throw new LogicException('No document element present. Document should not be empty!');
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

    /**
     * Given how PHPUnit nests suites, if there's more than one, the suite containing
     * actual tests will be two levels down the hierarchy from where we'd normally
     * find it with a single testsuite.
     *
     * @return integer
     */
    public function getRootTestSuiteNestingLevel()
    {
        $list = $this->xpath->query('//testsuite');
        if ($list->length == 1) {
            return 0;
        }
        return 1;
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

    /**
     * Adds a <php><env name="XXX" value="YYY"/></php> to set environment variables
     * and generates the <php> block if not present.
     *
     * @param string $name  Environment variable name
     * @param string $value Value of the variable to set
     */
    public function addEnvironmentVariable($name, $value)
    {
        $phpNodeList = $this->xpath->query('/phpunit/php');

        if ($phpNodeList->length) {
            $phpNode = $phpNodeList->item(0);
        } else {
            $phpNode = $this->dom->createElement('php');
            $this->rootElement->appendChild($phpNode);
        }

        $env = $this->dom->createElement('env');
        $phpNode->appendChild($env);

        $env->setAttribute('name', $name);
        $env->setAttribute('value', $value);
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

    public function replacePathsToAbsolutePaths(Visitor $pathVisitor, Visitor $wildcardVisitor)
    {
        $replacePaths = [
            '/phpunit/testsuites/testsuite/exclude',
            '//directory',
            '//file',
            '/phpunit/@bootstrap'
        ];

        $replaceQuery = implode('|', $replacePaths);

        $replaceNodes = $this->xpath->query($replaceQuery);

        foreach ($replaceNodes as $replace) {
            if (false !== strpos($replace->nodeValue, '*')) {
                $wildcardVisitor->visitElement($replace);
            } else {
                $pathVisitor->visitElement($replace);
            }
        }
    }

    public function generateXML()
    {
        return $this->dom->saveXML();
    }

    private function removeDocumentChildElementsByName($name)
    {
        $nodes = $this->xpath->query('/phpunit/' . $name);

        foreach ($nodes as $node) {
            $this->rootElement->removeChild($node);
        }
    }
}
