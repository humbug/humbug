<?php
/**
 * Created by PhpStorm.
 * User: thibaud
 * Date: 05/03/15
 * Time: 21:24
 */

namespace Humbug\MutantObservers;

use Humbug\Collector;
use Humbug\Mutant;
use Humbug\MutantResult;
use Humbug\MutantTestSuite;
use Humbug\MutantTestSuiteObserver;
use Humbug\Renderer\Text;
use Humbug\Utility\Performance;

class PerformanceObserver implements MutantTestSuiteObserver
{

    private $renderer;

    public function __construct(Text $renderer)
    {
        $this->renderer = $renderer;
    }

    public function onStartRun(MutantTestSuite $testSuite)
    {
        Performance::start();
    }

    public function onEndRun(MutantTestSuite $testSuite, Collector $resultCollector)
    {
        Performance::stop();

        $this->renderer->renderPerformanceData(
            Performance::getTimeString(),
            Performance::getMemoryUsageString()
        );

        Performance::downMemProfiler();
    }

    public function onMutantDone(MutantTestSuite $testSuite, Mutant $mutant, MutantResult $result, $index)
    {
        // TODO: Implement onMutantDone() method.
    }

    public function onShadowMutant(MutantTestSuite $testSuite, $mutationIndex)
    {
        // TODO: Implement onShadowMutant() method.
    }
}