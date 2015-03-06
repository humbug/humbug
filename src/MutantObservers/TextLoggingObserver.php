<?php

namespace Humbug\MutantObservers;

use Humbug\Collector;
use Humbug\Mutant;
use Humbug\MutantResult;
use Humbug\MutantTestSuite;
use Humbug\MutantTestSuiteObserver;
use Humbug\Renderer\Text;
use Humbug\Report\Text as TextReport;

class TextLoggingObserver implements MutantTestSuiteObserver
{

    private $textLogFile;

    private $renderer;

    public function __construct(Text $renderer, $textLogFile)
    {
        $this->textLogFile = $textLogFile;
        $this->renderer = $renderer;
    }

    public function onStartRun(MutantTestSuite $testSuite)
    { }

    public function onShadowMutant(MutantTestSuite $testSuite, $mutationIndex)
    { }

    public function onMutantDone(MutantTestSuite $testSuite, Mutant $mutant, MutantResult $result, $index)
    {
        $this->logText($this->renderer);
    }

    public function onEndRun(MutantTestSuite $testSuite, Collector $resultCollector)
    {
        $this->renderer->renderLogToText($this->textLogFile);
        $this->logText($this->renderer);

        $textReport = $this->prepareTextReport($resultCollector);
        $this->logText($this->renderer, $textReport);
    }

    private function prepareTextReport(Collector $collector)
    {
        $textReport = new TextReport();

        $out = $textReport->prepareMutantsReport($collector->getEscaped(), 'Escapes');

        if ($collector->getTimeoutCount() > 0) {
            $out .= PHP_EOL . $textReport->prepareMutantsReport($collector->getTimeouts(), 'Timeouts');
        }

        if ($collector->getErrorCount() > 0) {
            $out .= PHP_EOL . $textReport->prepareMutantsReport($collector->getErrors(), 'Errors');
        }

        return $out;
    }

    private function logText(Text $renderer, $output = null)
    {
        if ($this->textLogFile) {
            $logText = !is_null($output) ? $output : $renderer->getBuffer();

            file_put_contents(
                $this->textLogFile,
                $logText,
                FILE_APPEND
            );
        }
    }
}