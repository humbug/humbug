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

use Humbug\Adapter\Phpunit\XmlConfiguration\ObjectVisitor;
use Humbug\Adapter\Phpunit\XmlConfiguration\Visitor;
use Humbug\Container;
use Humbug\Exception\InvalidArgumentException;

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

    /**
     * Wrangle XML to create a PHPUnit configuration, based on the original, that
     * allows for more control over what tests are run, allows JUnit logging,
     * and ensures that Code Coverage (for Humbug use) whitelists all of the
     * relevant source code.
     *
     *
     * @return XmlConfiguration
     */
    public static function assemble(Container $container, $firstRun = false, array $testSuites = [])
    {
        $configurationDir = self::resolveConfigurationDir($container);

        $xmlConfigurationBuilder = new XmlConfigurationBuilder($configurationDir);

        if ($firstRun) {
            $xmlConfigurationBuilder->setPhpCoverage($container->getCacheDirectory() . '/coverage.humbug.php');
            $xmlConfigurationBuilder->setTextCoverage($container->getCacheDirectory() . '/coverage.humbug.txt');
            $xmlConfigurationBuilder->setCoverageFilter(self::getWhiteListSrc($container), self::getExcludeDirs($container));
            $xmlConfigurationBuilder->setTimeCollectionListener(self::getPathToTimeCollectorFile($container));
        } else {
            $xmlConfigurationBuilder->setFilterListener($testSuites, self::getPathToTimeCollectorFile($container));
        }

        $xmlConfiguration = $xmlConfigurationBuilder->build();

        if ($xmlConfiguration->hasOriginalBootstrap()) {
            $bootstrap = $xmlConfiguration->getOriginalBootstrap();
            $path = self::makeAbsolutePath($bootstrap, $configurationDir);

            //@todo Get rid off this side effect...
            $container->setBootstrap($path);
        }

        //todo get some information about what this listener is for
        $xmlConfiguration->addListener(new ObjectVisitor('\MyBuilder\PhpunitAccelerator\TestListener', [true]));

        //todo get some information about what tha hack is that
        if (!($xmlConfiguration->hasOriginalBootstrap())) {
            $bootstrap = self::findBootstrapFileInDirectories(
                $xmlConfiguration->getFirstSuiteDirectories(),
                $configurationDir
            );
            if ($bootstrap) {
                $xmlConfiguration->setBootstrap($bootstrap);

                //@todo Get rid off this side effect
                $container->setBootstrap($bootstrap);
            }
        }

        return $xmlConfiguration;
    }

    private static function findBootstrapFileInDirectories($directories, $configurationDir)
    {
        foreach ($directories as $directory) {
            $bootstrap = self::makeAbsolutePath($directory, $configurationDir);
            $bootstrap .= '/bootstrap.php';
            if (file_exists($bootstrap)) {
                return $bootstrap;
            }
        }
    }

    private static function getRealPathList($directories)
    {
        return array_map('realpath', $directories);
    }

    private static function getPathToTimeCollectorFile(Container $container)
    {
        return $container->getCacheDirectory() . '/phpunit.times.humbug.json';
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
     * @param Container $container
     * @return array
     */
    protected static function getWhiteListSrc(Container $container)
    {
        $srcList = $container->getSourceList();

        return isset($srcList->directories) ? self::getRealPathList($srcList->directories) : [];
    }

    /**
     * @param Container $container
     * @return array
     */
    protected static function getExcludeDirs(Container $container)
    {
        $srcList = $container->getSourceList();

        return isset($srcList->excludes) ? self::getRealPathList($srcList->excludes) : [];
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

    public function getFirstSuiteDirectories()
    {
        $directories = [];
        $directoriesList = $this->xpath->query('/phpunit/testsuites/testsuite[position()=1]/directory');

        foreach ($directoriesList as $directory) {
            $directories[] = $directory->nodeValue;
        }

        return $directories;
    }

    public function replacePathsToAbsolutePaths($configurationDir)
    {
        $suitesExcludes = $this->xpath->query('/phpunit/testsuites/testsuite/exclude');
        foreach ($suitesExcludes as $exclude) {
            $exclude->nodeValue = self::makeAbsolutePath($exclude->nodeValue, $configurationDir);
        }

        $directories = $this->xpath->query('//directory');
        foreach ($directories as $directory) {
            $directory->nodeValue = self::makeAbsolutePath($directory->nodeValue, $configurationDir);
        }

        $files = $this->xpath->query('//file');
        foreach ($files as $file) {
            $file->nodeValue = self::makeAbsolutePath($file->nodeValue, $configurationDir);
        }
    }

    public function generateXML()
    {
        return $this->dom->saveXML();
    }
}
