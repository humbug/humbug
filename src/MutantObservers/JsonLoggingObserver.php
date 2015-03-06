<?php

namespace Humbug\MutantObservers;

use Humbug\Collector;
use Humbug\Mutant;
use Humbug\MutantResult;
use Humbug\MutantTestSuite;
use Humbug\MutantTestSuiteObserver;
use Humbug\Renderer\Text;

class JsonLoggingObserver implements MutantTestSuiteObserver
{

    private $jsonLogFile;

    private $renderer;

    public function __construct(Text $renderer, $jsonLogFile)
    {
        $this->jsonLogFile = $jsonLogFile;
        $this->renderer = $renderer;
    }

    public function onStartRun(MutantTestSuite $testSuite)
    { }

    public function onShadowMutant(MutantTestSuite $testSuite, $mutationIndex)
    { }

    public function onMutantDone(MutantTestSuite $testSuite, Mutant $mutant, MutantResult $result, $index)
    { }

    public function onEndRun(MutantTestSuite $testSuite, Collector $resultCollector)
    {
        $this->renderer->renderLogToJson($this->jsonLogFile);
        $this->logJson($resultCollector);
    }

    protected function logJson(Collector $collector)
    {
        $vanquishedTotal = $collector->getVanquishedTotal();
        $measurableTotal = $collector->getMeasurableTotal();

        if ($measurableTotal !== 0) {
            $detectionRateTested  = round(100 * ($vanquishedTotal / $measurableTotal));
        } else {
            $detectionRateTested  = 0;
        }

        if ($collector->getTotalCount() !== 0) {
            $uncoveredRate = round(100 * ($collector->getShadowCount() / $collector->getTotalCount()));
            $detectionRateAll = round(100 * ($collector->getVanquishedTotal() / $collector->getTotalCount()));
        } else {
            $uncoveredRate = 0;
            $detectionRateAll = 0;
        }
        $out = [
            'summary' => [
                'total' => $collector->getTotalCount(),
                'kills' => $collector->getKilledCount(),
                'escapes' => $collector->getEscapeCount(),
                'errors' => $collector->getErrorCount(),
                'timeouts' => $collector->getTimeoutCount(),
                'notests' => $collector->getShadowCount(),
                'covered_score' => $detectionRateTested,
                'combined_score' => $detectionRateAll,
                'mutation_coverage' => (100 - $uncoveredRate)
            ],
            'escaped' => []
        ];

        $out = array_merge($out, $collector->toGroupedMutantArray());

        file_put_contents(
            $this->jsonLogFile,
            json_encode($out, JSON_PRETTY_PRINT)
        );
    }
}