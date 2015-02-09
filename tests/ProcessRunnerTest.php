<?php

namespace Humbug\Test;

use Humbug\Adapter\Phpunit;
use Humbug\ProcessRunner;
use Symfony\Component\Process\PhpProcess;

class ProcessRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testRunShouldNotFail()
    {
        $processRunner = new ProcessRunner();
        $testFrameworkAdapter = new Phpunit();

        $process = new PhpProcess('<?php
echo "ok 78 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testReturnsTokenEquivalentToLessThanOrEqualTo";
echo "ok 79 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testMutatesLessThanToLessThanOrEqualTo";
echo "ok 80 - Humbug\Test\Mutator\ConditionalBoundary\LessThanTest::testReturnsTokenEquivalentToLessThanOrEqualTo";
        ');

        $result = $processRunner->run($process , $testFrameworkAdapter);

        $this->assertFalse($result);
    }

    public function testRunShouldFail()
    {
        $processRunner = new ProcessRunner();
        $testFrameworkAdapter = new Phpunit();

        $i = 0;
        $callback = function() use (&$i) {
            $i++;
        };

        $process = new PhpProcess('<?php
echo "TAP version 13\n\r";
echo "not ok 82 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testMutatesLessThanToLessThanOrEqualTo\n\r";
echo "not ok 78 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testReturnsTokenEquivalentToLessThanOrEqualTo\n\r";
echo "not ok 79 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testMutatesLessThanToLessThanOrEqualTo\n\r";
echo "not ok 80 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testMutatesLessThanToLessThanOrEqualTo\n\r";
echo "not ok 81 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testMutatesLessThanToLessThanOrEqualTo\n";
echo "ok 81 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testMutatesLessThanToLessThanOrEqualTo\n";
        ');

        $result = $processRunner->run($process , $testFrameworkAdapter, $callback);

        $this->assertGreaterThan(0, $i);
        $this->assertTrue($result);
    }
}
 