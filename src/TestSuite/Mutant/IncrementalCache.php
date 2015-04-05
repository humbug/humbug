<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\TestSuite\Mutant;

use Humbug\Container;
use Humbug\File\Collector as FileCollector;
use Humbug\File\Collection as FileCollection;
use Humbug\TestSuite\Mutant\Collector as ResultCollector;
use Humbug\Adapter\AdapterAbstract;
use Humbug\Utility\CoverageData;
use Humbug\Exception\RuntimeException;

class IncrementalCache
{

    const FILES = 'source_files.json';

    const TESTS = 'test_files.json';

    const RESULTS = 'results.json';

    /**
     * @var FileCollector
     */
    private $fileCollector;

    /**
     * @var FileCollector
     */
    private $testCollector;

    /**
     * @var FileCollection
     */
    private $cachedFileCollection;

    /**
     * @var FileCollection
     */
    private $cachedTestCollection;

    /**
     * @var array
     */
    private $cachedResults;

    /**
     * @var Container
     */
    private $container;

    /**
     * ResultCollector
     */
    private $resultCollector;

    /**
     * @var string
     */
    private $workingCacheDirectory;

    /**
     * @param string $workingCacheDirectory
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->workingCacheDirectory = $container->getWorkingCacheDirectory();
        $this->fileCollector = new FileCollector(new FileCollection);
        $this->testCollector = new FileCollector(new FileCollection);
        $this->cachedFileCollection = $this->getCachedFileCollection(self::FILES);
        $this->cachedTestCollection = $this->getCachedFileCollection(self::TESTS);
        if ($this->hasResults()) {
            $this->loadResults();
        }
    }

    public function setResultCollector(ResultCollector $collector)
    {
        $this->resultCollector = $collector;
    }

    /**
     * @return FileCollector
     */
    public function getFileCollector()
    {
        return $this->fileCollector;
    }

    /**
     * @return FileCollector
     */
    public function getTestCollector()
    {
        return $this->testCollector;
    }

    public function hasResults()
    {
        return file_exists($this->workingCacheDirectory . '/' . self::RESULTS);
    }

    public function hasResultsFor($file)
    {
        return $this->hasResults() && isset($this->cachedResults[$file]);
    }

    public function getResultsFor($file)
    {
        if (!$this->hasResultsFor($file)) {
            throw new RuntimeException(sprintf(
                'There are no incremental cache results for file: %s', $file
            ));
        }
        return $this->cachedResults[$file];
    }

    private function loadResults()
    {
        $this->cachedResults = json_decode(
            file_get_contents($this->workingCacheDirectory . '/' . self::RESULTS),
            true
        );
    }

    /**
     * @return void
     */
    public function write()
    {
        $this->fileCollector->write($this->workingCacheDirectory . '/' . self::FILES);
        $this->testCollector->write($this->workingCacheDirectory . '/' . self::TESTS);
        file_put_contents(
            $this->workingCacheDirectory . '/' . self::RESULTS,
            json_encode(
                $this->resultCollector->toGroupedFileArray(),
                JSON_PRETTY_PRINT
            )
        );
    }

    /**
     * @param string $file
     * @return bool
     */
    public function hasModifiedSourceFiles($file)
    {
        if (!$this->cachedFileCollection->hasFile($file)) {
            return true;
        }

        $currentHash = $this->fileCollector->getCollection()->getFileHash($file);
        $previousHash = $this->cachedFileCollection->getFileHash($file);
        if ($currentHash !== $previousHash) {
            $result = true;
        }

        return false;
    }

    /**
     * @param CoverageData $coverage
     * @param string $file
     * @return bool
     */
    public function hasModifiedTestFiles(CoverageData $coverage, $file)
    {
        $result = false;
        $tests = $coverage->getAllTestClasses($file);
        foreach ($tests as $test) {
            $file = $this->container->getAdapter()->getClassFile($test, $this->container);
            $this->testCollector->collect($file);

            if (!$this->cachedTestCollection->hasFile($file)) {
                return true;
            }

            $currentHash = $this->testCollector->getCollection()->getFileHash($file);
            $previousHash = $this->cachedTestCollection->getFileHash($file);
            if ($currentHash !== $previousHash) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param string $cache
     * @return FileCollection
     */
    private function getCachedFileCollection($cache)
    {
        if (file_exists($this->workingCacheDirectory . '/' . $cache)) {
            $cachedFileCollection = new FileCollection(json_decode(
                file_get_contents($this->workingCacheDirectory . '/' . $cache),
                true
            ));
        } else {
            $cachedFileCollection = new FileCollection;
        }
        return $cachedFileCollection;
    }
}
