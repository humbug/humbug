<?php

namespace Humbug\TestSuiteObservers;

use Humbug\Renderer\Text;
use Humbug\TestSuiteObserver;
use Humbug\TestSuiteResult;
use Symfony\Component\Console\Output\OutputInterface;

class LoggingObserver implements TestSuiteObserver
{

    private $totalCount = 0;

    private $renderer;

    private $output;

    private $progressObserver;

    public function __construct(Text $renderer, OutputInterface $output, TestSuiteObserver $progressObserver)
    {
        $this->renderer = $renderer;
        $this->output = $output;
        $this->progressObserver = $progressObserver;
    }

    public function onStartSuite()
    {
        $this->renderer->renderPreTestIntroduction();
        $this->output->writeln("");
        $this->progressObserver->onStartSuite();
    }

    public function onProgress($count)
    {
        $this->totalCount++;
        $this->progressObserver->onProgress($count);
    }

    public function onStopSuite(TestSuiteResult $result)
    {
        $this->progressObserver->onStopSuite($result);
        $this->output->write(PHP_EOL . PHP_EOL);

        if (! $result->isSuccess()) {
            $this->renderer->renderInitialRunFail($result);

            return;
        }

        /**
         * Initial test run was a success!
         */
        $this->renderer->renderInitialRunPass($result, $this->totalCount);
    }

}