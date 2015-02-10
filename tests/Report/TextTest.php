<?php

namespace Humbug\Test\Report;

use Humbug\Mutant;
use Humbug\Report\Text;
use Symfony\Component\Process\Process;

class TextTest extends \PHPUnit_Framework_TestCase
{
    private $diff = <<<DIFF
--- Original
+++ New
@@ @@
         }
-        return false;
+        return true;
     }
 }

DIFF;

    private $mutation = [
        'mutator' => 'TestMutator',
        'class' => 'TestClass',
        'method' => 'testMethod',
        'file' => '/path/to/file'
    ];

    /**
     * @var Mutant
     */
    private $mutant;

    protected function createMutant($errorOutput)
    {
        $process = $this->getMock(Process::class, [], [], '', false);
        $process->expects($this->once())->method('getErrorOutput')->willReturn($errorOutput);

        $mutant = $this->getMock('Humbug\Mutant', [], [], '', false);

        $mutant->expects($this->once())->method('getMutation')->willReturn($this->mutation);
        $mutant->expects($this->once())->method('getDiff')->willReturn($this->diff);
        $mutant->expects($this->once())->method('getProcess')->willReturn($process);

        return $mutant;
    }

    public function testShouldPrepareSingleReport()
    {
        $mutant = $this->createMutant('');

        $mutantReport = (new Text())->prepareReportForMutant($mutant);

        $expectedReport =
            'TestMutator' . PHP_EOL .
            'Diff on TestClass::testMethod() in /path/to/file:' . PHP_EOL .
            $this->diff . PHP_EOL .
            PHP_EOL;

        $this->assertEquals($expectedReport, $mutantReport);
    }

    public function testShouldPrepareSingleReportWithError()
    {
        $mutant = $this->createMutant('Fatal error: This is test error');

        $mutantReport = (new Text())->prepareReportForMutant($mutant);

        $expectedError =
            'The following output was received on stderr:' . PHP_EOL .
            PHP_EOL .
            'Fatal error: This is test error' . PHP_EOL .
            PHP_EOL;

        $this->assertStringEndsWith($expectedError, $mutantReport);
    }
}
