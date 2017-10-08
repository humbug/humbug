<?php

namespace Humbug\Adapter\Phpunit\Listeners;

use Humbug\Phpunit\Listener\TimeCollectorListener;
use Humbug\Phpunit\Logger\JsonLogger;
use Humbug\Phpunit\Writer\JsonWriter;

class JsonLoggingTimeCollectorListener extends TimeCollectorListener
{
    public function __construct($logFile, $rootSuiteNestingLevel = 0)
    {
        $logger = new JsonLogger(new JsonWriter($logFile));

        parent::__construct($logger, $rootSuiteNestingLevel);
    }
}
