<?php

namespace Humbug\Adapter\Phpunit\Listeners;

use Humbug\Phpunit\Listener\TimeCollectorListener;
use Humbug\Phpunit\Logger\JsonLogger;

class JsonLoggingTimeCollectorListener extends TimeCollectorListener
{
    public function __construct($logFile, $rootSuiteNestingLevel = 0)
    {
        $logger = new JsonLogger($logFile);

        parent::__construct($logger, $rootSuiteNestingLevel);
    }
}
