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

use Humbug\Container;
use Humbug\Exception\NoCoveringTestsException;
use Humbug\MutableIterator;
use Humbug\Mutant;
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
    public function run(CoverageData $coverage, MutableIterator $mutables)
    {
        $this->mutableCount = count($mutables);
        $this->onStartRun();

        $collector = new Collector();
        $partition = new Partition();

        /**
         * MUTATION TESTING!
         */
        foreach ($mutables as $index => $mutable) {
            $mutations = $mutable->generate()->getMutations();
            $partition->addMutations($mutable, $index, $mutations);
            $mutable->cleanup();
        }

        foreach ($partition->getBatches($this->threadCount) as $index => $batch) {
            $this->runBatch($collector, $coverage, $batch, $index);
        }

        $coverage->cleanup();

        $this->onEndRun($collector);
    }

    /**
     * @param Collector $collector
     * @param CoverageData $coverage
     * @param array $batch
     * @param int $index
     */
    private function runBatch(
        Collector $collector,
        CoverageData $coverage,
        array $batch,
        $index
    ) {
        $processes = [];

        foreach ($batch as $mutation) {
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

                $processes[] = $this->processBuilder->build($mutant);
            } catch (NoCoveringTestsException $e) {
                /**
                 * No tests exercise the mutated line. We'll report
                 * the uncovered mutants separately and omit them
                 * from final score.
                 */
                $collector->collectShadow();
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

            $this->onMutantDone($process->getMutant(), $result, $index);
            $collector->collect($result);
        }
    }

    private function onStartRun()
    {
        foreach ($this->observers as $observer) {
            $observer->onStartRun($this);
        }
    }

    private function onShadowMutant($index)
    {
        foreach ($this->observers as $observer) {
            $observer->onShadowMutant($this, $index);
        }
    }

    private function onMutantDone(Mutant $mutant, Result $result, $index)
    {
        foreach ($this->observers as $observer) {
            $observer->onMutantDone($this, $mutant, $result, $index);
        }
    }

    private function onEndRun(Collector $collector)
    {
        foreach ($this->observers as $observer) {
            $observer->onEndRun($this, $collector);
        }
    }
}
