<?php

namespace Humbug;

interface TestSuiteObserver
{

    public function onStartSuite();

    public function onProgress($count);

    public function onStopSuite(TestSuiteResult $result);
}