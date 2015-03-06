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
use Humbug\Report\Text as TextReport;
use Humbug\TestSuite\Mutant\Collector;
use Humbug\TestSuite\Mutant\Observer;
use Humbug\TestSuite\Mutant\Result;
use Humbug\TestSuite\Mutant\Runner;

class TextLoggingObserver implements Observer
{

    private $textLogFile;

    private $renderer;

    public function __construct(Text $renderer, $textLogFile)
    {
        $this->textLogFile = $textLogFile;
        $this->renderer = $renderer;
    }

    public function onStartRun(Runner $testSuite)
    { }

    public function onShadowMutant(Runner $testSuite, $mutationIndex)
    { }

    public function onMutantDone(Runner $testSuite, Mutant $mutant, Result $result, $index)
    {
        $this->logText($this->renderer);
    }

    public function onEndRun(Runner $testSuite, Collector $resultCollector)
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