<?php

namespace Humbug;

interface MutantTestSuiteObserver
{
    public function onStartRun(MutantTestSuite $testSuite);

    public function onShadowMutant(MutantTestSuite $testSuite, $mutationIndex);

    public function onMutantDone(MutantTestSuite $testSuite, Mutant $mutant, MutantResult $result, $index);

    public function onEndRun(MutantTestSuite $testSuite, Collector $resultCollector);
}