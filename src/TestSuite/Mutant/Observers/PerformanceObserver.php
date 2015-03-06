<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant\Observers;

use Humbug\Mutant;
use Humbug\Renderer\Text;
use Humbug\TestSuite\Mutant\Collector;
use Humbug\TestSuite\Mutant\Observer;
use Humbug\TestSuite\Mutant\Result;
use Humbug\TestSuite\Mutant\Runner;
use Humbug\Utility\Performance;

class PerformanceObserver implements Observer
{

    private $renderer;

    public function __construct(Text $renderer)
    {
        $this->renderer = $renderer;
    }

    public function onStartRun(Runner $testSuite)
    {
        Performance::start();
    }

    public function onEndRun(Runner $testSuite, Collector $resultCollector)
    {
        Performance::stop();

        $this->renderer->renderPerformanceData(
            Performance::getTimeString(),
            Performance::getMemoryUsageString()
        );

        Performance::downMemProfiler();
    }

    public function onMutantDone(Runner $testSuite, Mutant $mutant, Result $result, $index)
    {
        // TODO: Implement onMutantDone() method.
    }

    public function onShadowMutant(Runner $testSuite, $mutationIndex)
    {
        // TODO: Implement onShadowMutant() method.
    }
}
