<?php

namespace Humbug\TestSuite\Unit;

use Humbug\Adapter\AdapterAbstract;
use Humbug\Container;
use Humbug\ProcessRunner;
use Symfony\Component\Process\PhpProcess;

class Runner
{

    private $adapter;

    private $process;

    private $coverageLogFile;

    /**
     * @var Observer[]
     */
    private $observers = [];

    public function __construct(AdapterAbstract $adapter, PhpProcess $process, $coverageLogFile)
    {
        $this->adapter = $adapter;
        $this->coverageLogFile = $coverageLogFile;
        $this->process = $process;
    }

    public function addObserver(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    public function run(Container $container)
    {
        $this->onStart();

        $hasFailure = $this->performInitialTestsRun($this->process, $this->adapter);
        $coverage = null;
        $lineCoverage = 0;

        if ($this->adapter->ok($this->process->getOutput()) && $this->process->getExitCode() === 0) {
            /**
             * Capture headline line coverage %.
             * Get code coverage data so we can determine which test suites or
             * or specifications need to be run for each mutation.
             */
            $coverage = $this->adapter->getCoverageData($container);
            $lineCoverage = $coverage->getLineCoverageFrom($this->coverageLogFile);
        }

        $result = new Result($this->process, $hasFailure, $coverage, $lineCoverage);

        $this->onStop($result);

        return $result;
    }

    private function performInitialTestsRun(
        PhpProcess $process,
        AdapterAbstract $testFrameworkAdapter
    ) {
        $observers = $this->observers;
        $onProgressCallback = function ($count) use ($observers) {
            foreach ($observers as $observer) {
                $observer->onProgress($count);
            }
        };

        return (new ProcessRunner())->run($process, $testFrameworkAdapter, $onProgressCallback);
    }

    private function onStart()
    {
        foreach ($this->observers as $observer) {
            $observer->onStartSuite();
        }
    }

    private function onStop(Result $result)
    {
        foreach ($this->observers as $observer) {
            $observer->onStopSuite($result);
        }
    }
}