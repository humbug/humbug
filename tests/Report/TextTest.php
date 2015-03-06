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
namespace Humbug\Test\Report;

use Humbug\Mutation;
use Humbug\Report\Text;

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

    private $mutation = null;

    protected function setup()
    {
        $this->mutation = new Mutation('/path/to/file', 1, 'TestClass', 'testMethod', 0, 'TestMutator');
    }

    public function testShouldPrepareSingleReport()
    {
        $mutant = $this->createMutantStub('');

        $mutantReport = (new Text())->prepareReportForMutant($mutant);

        $expectedReport = $this->getExpectedMutantReport();

        $this->assertEquals($expectedReport, $mutantReport);
    }

    public function testShouldPrepareSingleReportWithError()
    {
        $mutant = $this->createMutantStub('Fatal error: This is test error');

        $mutantReport = (new Text())->prepareReportForMutant($mutant);

        $expectedError =
            'The following output was received on stderr:' . PHP_EOL .
            PHP_EOL .
            'Fatal error: This is test error' . PHP_EOL .
            PHP_EOL;

        $this->assertEquals($this->getExpectedMutantReport() . $expectedError, $mutantReport);
    }

    public function testShouldPrepareAllMutantsReport()
    {
        $mutants = [
            $this->createMutant(),
            $this->createMutant(),
            $this->createMutant()
        ];

        $textReport = $this->getMock('Humbug\Report\Text', ['prepareReportForMutant']);

        $textReport->expects($this->at(0))->method('prepareReportForMutant')->with($mutants[0]);
        $textReport->expects($this->at(1))->method('prepareReportForMutant')->with($mutants[1]);
        $textReport->expects($this->at(2))->method('prepareReportForMutant')->with($mutants[2]);

        $mutantsReport = $textReport->prepareMutantsReport($mutants, 'Mutants');

        $expectedMutantsGroup =
            '------' . PHP_EOL .
            'Mutants' . PHP_EOL .
            '------' . PHP_EOL .
            PHP_EOL;

        $this->assertStringStartsWith($expectedMutantsGroup, $mutantsReport);
        $this->assertContains(PHP_EOL . '1) ', $mutantsReport);
        $this->assertContains(PHP_EOL . '2) ', $mutantsReport);
        $this->assertContains(PHP_EOL . '3) ', $mutantsReport);
    }

    private function createMutantStub($errorOutput)
    {
        $process = $this->getMock('Symfony\Component\Process\Process', [], [], '', false);
        $process->expects($this->once())->method('getErrorOutput')->willReturn($errorOutput);

        $mutant = $this->createMutant();

        $mutant->expects($this->once())->method('getMutation')->willReturn($this->mutation);
        $mutant->expects($this->once())->method('getDiff')->willReturn($this->diff);
        $mutant->expects($this->once())->method('getProcess')->willReturn($process);

        return $mutant;
    }

    private function createMutant()
    {
        return $this->getMock('Humbug\Mutant', [], [], '', false);
    }

    private function getExpectedMutantReport()
    {
        return
            'TestMutator' . PHP_EOL .
            'Diff on TestClass::testMethod() in /path/to/file:' . PHP_EOL .
            $this->diff . PHP_EOL .
            PHP_EOL;
    }
}
