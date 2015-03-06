<?php

namespace Humbug;

use Humbug\Adapter\AdapterAbstract;
use Symfony\Component\Process\PhpProcess;

class TestSuiteRunner
{

    private $adapter;

    private $process;

    private $coverageLogFile;

    /**
     * @var TestSuiteObserver[]
     */
    private $observers = [];

    public function __construct(AdapterAbstract $adapter, PhpProcess $process, $coverageLogFile)
    {
        $this->adapter = $adapter;
        $this->coverageLogFile = $coverageLogFile;
        $this->process = $process;
    }

    public function addObserver(TestSuiteObserver $observer)
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
            $coverage = $container->getAdapter()->getCoverageData($container);
            $lineCoverage = $coverage->getLineCoverageFrom(
                $container->getCacheDirectory() . $this->coverageLogFile
            );
        }

        $result = new TestSuiteResult($this->process, $hasFailure, $coverage, $lineCoverage);

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

    private function onStop(TestSuiteResult $result)
    {
        foreach ($this->observers as $observer) {
            $observer->onStopSuite($result);
        }
    }
}