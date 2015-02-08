<?php

namespace Humbug;

use Humbug\Adapter\AdapterAbstract;
use Symfony\Component\Process\PhpProcess;

class ProcessRunner
{
    public function run(
        PhpProcess $process,
        AdapterAbstract $testFrameworkAdapter,
        \Closure $onProgressCallback = null
    ) {
        $hasFailure = false;

        $process->start();
        usleep(1000);
        while ($process->isRunning()) {
            usleep(2500);
            if (($count = $testFrameworkAdapter->hasOks($process->getOutput()))) {
                if ($onProgressCallback) {
                    $onProgressCallback($count);
                }
                $process->clearOutput();
            } elseif (!$testFrameworkAdapter->ok($process->getOutput())) {
                sleep(1);
                $hasFailure = true;
                break;
            }
        }
        $process->stop();

        return $hasFailure;
    }
}
