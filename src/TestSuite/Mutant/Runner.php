<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant;

use Humbug\Exception\NoCoveringTestsException;
use Humbug\MutableIterator;
use Humbug\Mutant;
use Humbug\Mutable;
use Humbug\TestSuite\Mutant\IncrementalCache;
use Humbug\Utility\CoverageData;
use Humbug\Utility\ParallelGroup;

class Runner
{

    /**
     * @var FileGenerator
     */
    private $mutantGenerator;

    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @var int
     */
    private $mutableCount = 0;

    /**
     * @var int
     */
    private $threadCount;

    /**
     * @var Observer[]
     */
    private $observers = [];

    /**
     * @param FileGenerator $mutantGenerator
     * @param ProcessBuilder $processBuilder
     * @param string $baseDirectory
     * @param int $threadCount
     */
    public function __construct(FileGenerator $mutantGenerator, ProcessBuilder $processBuilder, $baseDirectory, $threadCount = 1)
    {
        $this->mutantGenerator = $mutantGenerator;
        $this->processBuilder = $processBuilder;

        $this->threadCount = max((int)$threadCount, 1);
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * @param Observer $observer
     */
    public function addObserver(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function getMutableCount()
    {
        return $this->mutableCount;
    }

    /**
     * @param CoverageData $coverage
     * @param MutableIterator $mutables
     */
    public function run(CoverageData $coverage, MutableIterator $mutables, IncrementalCache $cache = null)
    {
        $this->mutableCount = count($mutables);
        $this->onStartRun();

        $collector = new Collector();
        $partition = new PartitionBuilder();

        /**
         * MUTATION TESTING!
         */
        foreach ($mutables as $index => $mutable) {
            if (!is_null($cache)) {
                $cache->setResultCollector($collector);
                $cache->getFileCollector()->collect($mutable->getFilename());
            }

            $mutations = $mutable->generate()->getMutations();

            $partition->addMutations($mutable, $index, $mutations);
            $this->onProcessedMutable($mutable);
            $mutable->cleanup();
        }

        $this->onMutationsGenerated();

        foreach ($partition->getPartitions($this->threadCount) as $batch) {
            $this->runBatch($collector, $coverage, $batch, $cache);
        }

        $coverage->cleanup();

        $this->onEndRun($collector);

        $this->mutantGenerator->cleanup();
    }

    /**
     * @param Collector $collector
     * @param CoverageData $coverage
     * @param array $batch
     */
    private function runBatch(
        Collector $collector,
        CoverageData $coverage,
        array $batch,
        IncrementalCache $cache = null
    ) {
        $processes = [];

        foreach ($batch as $batchItem) {
            list($index, $mutation) = $batchItem;

            try {
                $coverage->loadCoverageFor($mutation->getFile());
                /**
                 * Unleash the Mutant!
                 */
                $mutant = new Mutant(
                    $mutation,
                    $this->mutantGenerator,
                    $coverage,
                    $this->baseDirectory
                );

                if (!$mutant->hasTests()) {
                    throw new NoCoveringTestsException(
                        'Current mutant has no covering tests'
                    );
                }

                if (!is_null($cache)) {
                    $hit = $this->runCache($cache, $coverage, $collector, $mutation->getFile(), $index);
                    if ($hit === true) {
                        continue;
                    }
                }

                $processes[] = $this->processBuilder->build($mutant, $index);
            } catch (NoCoveringTestsException $e) {
                $shadow = new Mutant(
                    $mutation,
                    $this->mutantGenerator,
                    $coverage,
                    $this->baseDirectory
                );
                $collector->collectShadow($shadow);
                $this->onShadowMutant($index);
            }
        }

        /**
         * Check if the whole batch has been eliminated as uncovered
         * by any tests
         */
        if (count($processes) == 0) {
            return;
        }

        $group = new ParallelGroup($processes);
        $group->run();

        foreach ($processes as $process) {
            /**
             * Handle the defined result for each process
             */
            $result = $process->getResult();

            $this->onMutantDone($result, $process->getMutableIndex());
            $collector->collect($result);
        }
    }

    private function runCache(
        IncrementalCache $cache,
        CoverageData $coverage,
        Collector $collector,
        $mutableFile,
        $index
    ) {
        static $fileHits = [];
        static $cacheHits = [];

        if (in_array($mutableFile, $cacheHits)) {
            return true;
        } elseif (in_array($mutableFile, $fileHits)) {
            return false;
        }

        $fileHits[] = $mutableFile;

        $testFilesHaveChanged = $cache->hasModifiedTestFiles(
            $coverage,
            $mutableFile
        );

        $sourceFilesHaveChanged = $cache->hasModifiedSourceFiles(
            $mutableFile
        );

        if ($cache->hasResultsFor($mutableFile)
        && $testFilesHaveChanged === false && $sourceFilesHaveChanged === false) {
            $resultSet = $cache->getResultsFor($mutableFile);
            foreach ($resultSet as $result) {
                if ($result['isShadow'] === false) {
                    $resultObject = unserialize($result['result']);
                    $collector->collect($resultObject);
                    $this->onMutantDone(
                        $resultObject,
                        $index
                    );
                } else {
                    $mutantObject = unserialize($result['result']);
                    $collector->collectShadow($mutantObject);
                    $this->onShadowMutant($index);
                }
            }
            $cacheHits[] = $mutableFile;
            return true;
        }
        return false;
    }

    private function onStartRun()
    {
        foreach ($this->observers as $observer) {
            $observer->onStartRun($this);
        }
    }

    private function onProcessedMutable(Mutable $mutable)
    {
        foreach ($this->observers as $observer) {
            $observer->onProcessedMutable($mutable);
        }
    }

    private function onMutationsGenerated()
    {
        foreach ($this->observers as $observer) {
            $observer->onMutationsGenerated();
        }
    }

    private function onShadowMutant($index)
    {
        foreach ($this->observers as $observer) {
            $observer->onShadowMutant($this, $index);
        }
    }

    private function onMutantDone(Result $result, $index)
    {
        foreach ($this->observers as $observer) {
            $observer->onMutantDone($this, $result, $index);
        }
    }

    private function onEndRun(Collector $collector)
    {
        foreach ($this->observers as $observer) {
            $observer->onEndRun($this, $collector);
        }
    }
}
