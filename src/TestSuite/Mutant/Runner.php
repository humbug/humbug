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
use Humbug\Mutable;
use Humbug\Mutant;
use Humbug\Utility\CoverageData;
use Humbug\Utility\ParallelGroup;

class Runner
{
    /**
     * @var Mutable[]
     */
    private $mutables;

    /**
     * @var int
     */
    private $mutableCount;

    /**
     * @var int
     */
    private $threadCount;

    /**
     * @var Observer[]
     */
    private $observers = [];

    public function __construct(array $mutables, $threadCount = 1)
    {
        $this->mutables = $mutables;
        $this->mutableCount = count($mutables);
        $this->threadCount = max((int)$threadCount, 1);
    }

    /**
     * @return int
     */
    public function getMutableCount()
    {
        return $this->mutableCount;
    }

    /**
     * @return Mutable[]
     */
    public function getMutables()
    {
        return $this->mutables;
    }

    /**
     * @param Observer $observer
     */
    public function addObserver(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * @param Container $container
     * @param CoverageData $coverage
     */
    public function run(Container $container, CoverageData $coverage)
    {
        $this->onStartRun();

        $baseDirectory = $container->getBaseDirectory();
        $mutantGenerator = new FileGenerator($container);
        $processBuilder = new ProcessBuilder($container);
        $collector = new Collector();

        /**
         * MUTATION TESTING!
         */
        foreach ($this->mutables as $i => $mutable) {
            $mutations = $mutable->generate()->getMutations();
            $batches = array_chunk($mutations, $this->threadCount);

            try {
                $coverage->loadCoverageFor($mutable->getFilename());
            } catch (NoCoveringTestsException $e) {
                $shadowCount = count($mutations);

                for ($i = 0; $i < $shadowCount; $i++) {
                    $collector->collectShadow();
                    $this->onShadowMutant($i);
                }

                continue;
            }

            foreach ($batches as $batch) {
                $this->runBatch(
                    $processBuilder,
                    $mutantGenerator,
                    $collector,
                    $coverage,
                    $baseDirectory,
                    $batch,
                    $i
                );
            }

            $mutable->cleanup();
        }

        $coverage->cleanup();
        $this->onEndRun($collector);
    }

    /**
     * @param ProcessBuilder $processBuilder
     * @param FileGenerator $mutantGenerator
     * @param Collector $collector
     * @param CoverageData $coverage
     * @param $batch
     * @param $i
     */
    private function runBatch(
        ProcessBuilder $processBuilder,
        FileGenerator $mutantGenerator,
        Collector $collector,
        CoverageData $coverage,
        $baseDirectory,
        $batch,
        $i
    ) {
        $processes = [];

        foreach ($batch as $mutation) {
            try {
                /**
                 * Unleash the Mutant!
                 */
                $processes[] = $processBuilder->build(
                    new Mutant($mutation, $mutantGenerator, $coverage, $baseDirectory)
                );
            } catch (NoCoveringTestsException $e) {
                /**
                 * No tests exercise the mutated line. We'll report
                 * the uncovered mutants separately and omit them
                 * from final score.
                 */
                $collector->collectShadow();
                $this->onShadowMutant($i);
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

            $this->onMutantDone($process->getMutant(), $result, $i);
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
