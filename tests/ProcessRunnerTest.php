<?php

/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 *
 * @author     rafal.wartalski@gmail.com
 */

namespace Humbug\Test;

use Humbug\Adapter\AdapterAbstract;
use Humbug\Adapter\Phpunit;
use Humbug\ProcessRunner;
use Symfony\Component\Process\PhpProcess;

class ProcessRunnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProcessRunner
     */
    private $processRunner;

    /**
     * @var AdapterAbstract
     */
    private $testFrameworkAdapter;

    protected function setUp()
    {
        $this->processRunner = new ProcessRunner();
        $this->testFrameworkAdapter = new Phpunit();
    }

    public function testRunShouldNotFail()
    {
        $process = $this->createOkProcess();

        $result = $this->runProcess($process);

        $this->assertFalse($result);
    }

    public function testRunShouldFail()
    {
        $process = $this->createNotOkProcess();

        $result = $this->runProcess($process);

        $this->assertTrue($result);
    }

    public function testShouldInvokeOnProgressCallback()
    {
        $executedCount = 0;

        $onProgressCallback = function () use (&$executedCount) {
            $executedCount++;
        };

        $process = $this->createOkProcess();

        $this->runProcess($process, $onProgressCallback);

        $this->assertGreaterThan(0, $executedCount);
    }

    /**
     * @return PhpProcess
     */
    private function createOkProcess()
    {
        $process = new PhpProcess('<?php
echo "##teamcity[testFinished name=\'Some sort of test finished\' duration=\'0\' flowId=\'15199\']\r\n";
        ');

        return $process;
    }

    private function runProcess(PhpProcess $process, $onProgressCallback = null)
    {
        return $this->processRunner->run($process, $this->testFrameworkAdapter, $onProgressCallback);
    }

    /**
     * @return PhpProcess
     */
    private function createNotOkProcess()
    {
        $process = new PhpProcess('<?php
echo "##teamcity[testFailed name=\'Some sort of test finished\' duration=\'0\' flowId=\'15199\']\r\n";
        ');
        return $process;
    }
}
