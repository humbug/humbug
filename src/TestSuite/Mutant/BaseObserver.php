<?php
/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\TestSuite\Mutant;

use Humbug\Mutable;
use Humbug\TestSuite\Mutant\Collector;
use Humbug\TestSuite\Mutant\Observer;
use Humbug\TestSuite\Mutant\Result;
use Humbug\TestSuite\Mutant\Runner;

abstract class BaseObserver implements Observer
{

    public function onStartRun(Runner $testSuite)
    {
    }

    public function onProcessedMutable(Mutable $mutable)
    {
    }

    public function onMutationsGenerated()
    {
    }

    public function onShadowMutant(Runner $testSuite, $mutationIndex)
    {
    }

    public function onMutantDone(Runner $testSuite, Result $result, $index)
    {
    }

    public function onEndRun(Runner $testSuite, Collector $resultCollector)
    {
    }
}
