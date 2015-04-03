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
use Symfony\Component\Console\Input\InputInterface;

class PerformanceObserver implements Observer
{

    private $renderer;

    private $isDisabled = false;

    public function __construct(Text $renderer, InputInterface $input)
    {
        $this->renderer = $renderer;
        if ($input->getOption('no-progress-bar')) {
            $this->isDisabled = true;
        }
    }

    public function onStartRun(Runner $testSuite)
    {
        Performance::start();
    }

    public function onEndRun(Runner $testSuite, Collector $resultCollector)
    {
        Performance::stop();

        if (!$this->isDisabled) {
            $this->renderer->renderPerformanceData(
                Performance::getTimeString(),
                Performance::getMemoryUsageString()
            );
        }

        Performance::downMemProfiler();
    }

    public function onMutantDone(Runner $testSuite, Result $result, $index)
    {
    }

    public function onShadowMutant(Runner $testSuite, $mutationIndex)
    {
    }
}
