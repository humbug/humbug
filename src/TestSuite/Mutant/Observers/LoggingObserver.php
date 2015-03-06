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
use Symfony\Component\Console\Output\OutputInterface;

class LoggingObserver implements Observer
{

    private $renderer;

    private $output;

    public function __construct(Text $renderer, OutputInterface $output)
    {
        $this->renderer = $renderer;
        $this->output = $output;
    }

    public function onStartRun(Runner $testSuite)
    {
        /**
         * Message re Mutation Testing starting
         */
        $this->renderer->renderMutationTestingStart($testSuite->getMutableCount());
        $this->output->write(PHP_EOL);
    }

    public function onMutantDone(Runner $testSuite, Mutant $mutant, Result $result, $index)
    {
        $this->renderer->renderProgressMark($result, $testSuite->getMutableCount(), $index);
    }

    public function onEndRun(Runner $testSuite, Collector $resultCollector)
    {
        /**
         * Render summary report with stats
         */
        $this->output->write(PHP_EOL);
        $this->renderer->renderSummaryReport($resultCollector);
        $this->output->write(PHP_EOL);

    }

    public function onShadowMutant(Runner $testSuite, $mutationIndex)
    {
        $this->renderer->renderShadowMark($testSuite->getMutableCount(), $mutationIndex);
    }
}