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

use Humbug\Adapter\Phpunit\XmlConfiguration\AcceleratorListener;
use Humbug\Adapter\Phpunit\XmlConfiguration\FastestFirstFilter;
use Humbug\Adapter\Phpunit\XmlConfiguration\IncludeOnlyFilter;
use Humbug\Adapter\Phpunit\XmlConfiguration\TimeCollectorListener;
use Humbug\Adapter\Phpunit\XmlConfiguration\Visitor;
use Humbug\Container;
use Humbug\Exception\RuntimeException;
use Humbug\Exception\InvalidArgumentException;

class XmlConfiguration
{
    private static $listeners;

    private static $hasBootstrap;

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

    public function __construct(\DOMDocument $dom)
    {
        if (!$dom->documentElement) {
            throw new \LogicException('No document element present. Document should not be empty!');
        }

        $this->dom = $dom;
        $this->xpath = new \DOMXPath($this->dom);
        $this->rootElement = $this->dom->documentElement;
    }

    /**
     * Wrangle XML to create a PHPUnit configuration, based on the original, that
     * allows for more control over what tests are run, allows JUnit logging,
     * and ensures that Code Coverage (for Humbug use) whitelists all of the
     * relevant source code.
     *
     *
     * @return string
     */
    public static function assemble(Container $container, $firstRun = false, array $testSuites = [])
    {
        self::$hasBootstrap = false;

        $configurationDir = self::resolveConfigurationDir($container);

        $configurationFile = (new ConfigurationLocator())->locate($configurationDir);

        if (!empty($configurationDir)) {
            $configurationDir .= '/';
        }

        $dom = (new ConfigurationLoader())->load($configurationFile);

        $xmlConfiguration = new XmlConfiguration($dom);

        if ($xmlConfiguration->hasBootstrap()) {
            self::$hasBootstrap = true;
            $bootstrap = $xmlConfiguration->getBootstrap();
            $path = self::makeAbsolutePath($bootstrap, $configurationDir);

            //@todo Get rid off this side effect...
            $container->setBootstrap($path);
        }

        $xmlConfiguration->setBootstrap(self::getNewBootstrapPath());
        $xmlConfiguration->turnOffCacheTokens();

        $xmlConfiguration->cleanupLoggers();
        $xmlConfiguration->cleanupFilters();
        $xmlConfiguration->cleanupListeners();

        $xmlConfiguration->addListener(new AcceleratorListener());

        $xpath = new \DOMXPath($dom);

        self::$listeners = $xpath->query('/phpunit/listeners')->item(0);

        /**
         * On first runs collect a test log and also generate code coverage
         */
        if ($firstRun === true) {
            self::handleLogging($container, $dom);
            $xmlConfiguration->addListener(new TimeCollectorListener(self::getPathToTimeCollectorFile($container)));
        } else {
            self::handleTestSuiteFilterListener($testSuites, $container, $dom);
        }

        /** @var \DOMNode[] $nodesToRemove */
        $nodesToRemove = array();
        $suites = $xpath->query('/phpunit/testsuites/testsuite');
        foreach ($suites as $suite) {
            // DOMNodeList's Traversable implementation is a bit unpredictable.
            // Iterate over the child nodes using a for loop rather than a
            // foreach so that we can append new children without these being
            // iterated over again.
            $length = $suite->childNodes->length;
            for ($i = 0; $i < $length; $i++) {
                $node = $suite->childNodes->item($i);
                if ($node instanceof \DOMElement
                && ($node->tagName == 'directory'
                || $node->tagName == 'exclude'
                || $node->tagName == 'file')) {
                    $fullPath = $configurationDir . '/' . $node->nodeValue;
                    // Check if the paths exist.
                    $paths = glob($fullPath);
                    if (0 === count($paths)) {
                        // It's no problem if an exclude path is missing.
                        if ($node->tagName !== 'exclude') {
                            throw new RuntimeException('Unable to locate file specified in testsuites: ' . $fullPath);
                        }
                    } else {
                        foreach ($paths as $path) {
                            $clone = $node->cloneNode();
                            $clone->nodeValue = self::makeAbsolutePath($path, getcwd());
                            $node->parentNode->appendChild($clone);
                        }
                    }
                    // Mark the original unprocessed node to be removed.
                    $nodesToRemove[] = $node;
                }
            }
        }

        // Remove the original unprocessed nodes. This cannot be done inside the
        // loop that processes the nodes since the removal of a node causes
        // DOMNodeList to reset its internal array keys.
        foreach ($nodesToRemove as $node) {
            $node->parentNode->removeChild($node);
        }

        /**
         * Set any remaining file & directory references to realpaths
         */
        $directories = $xpath->query('//directory');
        foreach ($directories as $directory) {
            $directory->nodeValue = self::makeAbsolutePath($directory->nodeValue, dirname($configurationFile));
        }
        $files = $xpath->query('//file');
        foreach ($files as $file) {
            $file->nodeValue = self::makeAbsolutePath($file->nodeValue, dirname($configurationFile));
        }

        $suite1 = $xpath->query('/phpunit/testsuites/testsuite')->item(0);
        if (is_a($suite1, 'DOMElement')) {
            self::handleSuite($suite1, $configurationFile, $container, $dom);
        }
        
        $saveFile = $container->getCacheDirectory() . '/phpunit.humbug.xml';
        $dom->save($saveFile);

        return $saveFile;
    }

    private static function handleSuite(\DOMElement $suite, $configFile, Container $container, \DOMDocument $dom)
    {
        foreach ($suite->childNodes as $child) {
            // phpunit.xml may omit bootstrap location but grab it automatically - include explicitly
            if (self::$hasBootstrap === false && $child instanceof \DOMElement && $child->tagName == 'directory') {
                $bootstrapDir = self::makeAbsolutePath($child->nodeValue, dirname($configFile));
                if (file_exists($bootstrapDir . '/bootstrap.php')) {
                    $dom->documentElement->setAttribute('bootstrap', $bootstrapDir . '/bootstrap.php');

                    //@todo Get rid off this side effect
                    $container->setBootstrap($bootstrapDir . '/bootstrap.php');
                    self::$hasBootstrap = true;
                }
            }
        }
    }

    private static function handleLogging(Container $container, \DOMDocument $dom)
    {
        // add new logs as needed
        $logging = $dom->createElement('logging');
        $dom->documentElement->appendChild($logging);

        // php coverage
        $log = $dom->createElement('log');
        $log->setAttribute('type', 'coverage-php');
        $log->setAttribute(
            'target',
            $container->getCacheDirectory() . '/coverage.humbug.php'
        );
        $logging->appendChild($log);
        $log2 = $dom->createElement('log');
        $log2->setAttribute('type', 'coverage-text');
        $log2->setAttribute(
            'target',
            $container->getCacheDirectory() . '/coverage.humbug.txt'
        );
        $logging->appendChild($log2);

        /**
         * While we're here, reset code coverage filter to meet the known source
         * code constraints.
         */
        $filter = $dom->createElement('filter');
        $whitelist = $dom->createElement('whitelist');
        $dom->documentElement->appendChild($filter);
        $filter->appendChild($whitelist);
        $source = $container->getSourceList();
        if (isset($source->directories)) {
            foreach ($source->directories as $d) {
                $directory = $dom->createElement('directory', realpath($d));
                $directory->setAttribute('suffix', '.php');
                $whitelist->appendChild($directory);
            }
        }
        if (isset($source->excludes)) {
            $exclude = $dom->createElement('exclude');
            foreach ($source->excludes as $d) {
                $directory = $dom->createElement('directory', realpath($d));
                $exclude->appendChild($directory);
            }
            $whitelist->appendChild($exclude);
        }
    }

    private static function getPathToTimeCollectorFile(Container $container)
    {
        return $container->getCacheDirectory() . '/phpunit.times.humbug.json';
    }

    private static function handleTestSuiteFilterListener(array $testSuites, Container $container, \DOMDocument $dom)
    {
        $listener = $dom->createElement('listener');
        self::$listeners->appendChild($listener);
        $listener->setAttribute('class', '\Humbug\Phpunit\Listener\FilterListener');
        $arguments = $dom->createElement('arguments');
        $listener->appendChild($arguments);

        /**
         * Add the IncludeOnly Filter
         */
        $includeOnly = $dom->createElement('object');
        $arguments->appendChild($includeOnly);

        (new IncludeOnlyFilter($testSuites))->visitElement($includeOnly);

        /**
         * Add the FastestFirst Filter
         */
        $fastestFirst = $dom->createElement('object');
        $arguments->appendChild($fastestFirst);

        (new FastestFirstFilter(self::getPathToTimeCollectorFile($container)))->visitElement($fastestFirst);
    }

    private static function makeAbsolutePath($name, $workingDir)
    {
        // @see https://github.com/symfony/Config/blob/master/FileLocator.php#L83
        if ('/' === $name[0]
            || '\\' === $name[0]
            || (strlen($name) > 3 && ctype_alpha($name[0]) && $name[1] == ':' && ($name[2] == '\\' || $name[2] == '/'))
        ) {
            if (!file_exists($name)) {
                throw new InvalidArgumentException("$name does not exist");
            }

            return realpath($name);
        }

        $relativePath = $workingDir.DIRECTORY_SEPARATOR.$name;
        $glob = glob($relativePath);
        if (file_exists($relativePath) || !empty($glob)) {
            return realpath($relativePath);
        }

        throw new InvalidArgumentException("Could not find file $name working from $workingDir");
    }

    /**
     * @param Container $container
     * @return string
     */
    private static function resolveConfigurationDir(Container $container)
    {
        $configurationDir = $container->getTestRunDirectory();

        if (empty($configurationDir)) {
            $configurationDir = $container->getBaseDirectory();
        }

        return $configurationDir;
    }

    /**
     * @return string
     */
    private static function getNewBootstrapPath()
    {
        return sys_get_temp_dir() . '/humbug.phpunit.bootstrap.php';
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
        return $this->rootElement->setAttribute('bootstrap', $bootstrap);
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
        }else {
            $listeners = $this->dom->createElement('listeners');
            $this->rootElement->appendChild($listeners);
        }

        $listener = $this->dom->createElement('listener');
        $listeners->appendChild($listener);

        $visitor->visitElement($listener);
    }
}
