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

use Humbug\File\Collector as FileCollector;
use Humbug\File\Collection as FileCollection;

class IncrementalCache
{

    private $fileCollector;

    private $testCollector;

    private $cachedFileCollection;

    private $cachedTestCollection;

    private $cachedResults;

    /**
     * @param string $workingCacheDirectory
     */
    public function __construct($workingCacheDirectory)
    {
        $this->fileCollector = new FileCollector(new FileCollection);
        $this->testCollector = new FileCollector(new FileCollection);
        $this->cachedFileCollection = $this->getCachedFileCollection('source_files.json');
        $this->cachedTestCollection = $this->getCachedFileCollection('test_files.json');

        if (file_exists($workingCacheDirectory . '/results.json')) {
            $this->cachedResults = json_decode(file_get_contents(
                $workingCacheDirectory . '/results.json'
            ), true);
        }
    }

    public function getFileCollector()
    {
        return $this->fileCollector;
    }

    public function getTestCollector()
    {
        return $this->testCollector;
    }

    /**
     * @param string $cache
     */
    private function getCachedFileCollection($cache)
    {
        if (file_exists($this->container->getWorkingCacheDirectory() . '/' . $cache)) {
            $cachedFileCollection = new FileCollection(json_decode(
                file_get_contents($this->container->getWorkingCacheDirectory() . '/' . $cache),
                true
            ));
        } else {
            $cachedFileCollection = new FileCollection;
        }
        return $cachedFileCollection;
    }

    private function testFilesHaveChanged(
        FileCollector $collector,
        FileCollection $cached,
        \Humbug\Utility\CoverageData $coverage,
        AdapterAbstract $adapter,
        $file)
    {
        $result = false;
        $tests = $coverage->getAllTestClasses($file);
        foreach ($tests as $test) {
            $file = $adapter->getClassFile($test, $this->container);
            $collector->collect($file);
            if (!$cached->hasFile($file)
            || $collector->getCollection()->getFileHash($file) !== $cached->getFileHash($file)) {
                $result = true;
            }
        }
        return $result;
    }
}
