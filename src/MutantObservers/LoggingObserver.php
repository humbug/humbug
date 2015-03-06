<?php

namespace Humbug\MutantObservers;

use Humbug\Collector;
use Humbug\Mutant;
use Humbug\MutantResult;
use Humbug\MutantTestSuite;
use Humbug\MutantTestSuiteObserver;
use Humbug\Renderer\Text;
use Symfony\Component\Console\Output\OutputInterface;

class LoggingObserver implements MutantTestSuiteObserver
{

    private $renderer;

    private $output;

    private $total = 0;

    public function __construct(Text $renderer, OutputInterface $output)
    {
        $this->renderer = $renderer;
        $this->output = $output;
    }

    public function onStartRun(MutantTestSuite $testSuite)
    {
        /**
         * Message re Mutation Testing starting
         */
        $this->renderer->renderMutationTestingStart($testSuite->getMutableCount());
        $this->output->write(PHP_EOL);
    }

    public function onMutantDone(MutantTestSuite $testSuite, Mutant $mutant, MutantResult $result, $index)
    {
        $this->renderer->renderProgressMark($result, $testSuite->getMutableCount(), $index);
    }

    public function onEndRun(MutantTestSuite $testSuite, Collector $resultCollector)
    {
        /**
         * Render summary report with stats
         */
        $this->output->write(PHP_EOL);
        $this->renderer->renderSummaryReport($resultCollector);
        $this->output->write(PHP_EOL);

    }

    public function onShadowMutant(MutantTestSuite $testSuite, $mutationIndex)
    {
        $this->renderer->renderShadowMark($testSuite->getMutableCount(), $mutationIndex);
    }
}