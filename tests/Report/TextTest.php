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
use Humbug\TestSuite\Mutant\Result;

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
        $result = $this->createMutantResultStub('');

        $mutantReport = (new Text())->prepareReportForMutant($result);

        $expectedReport = $this->getExpectedMutantReport();

        $this->assertEquals($expectedReport, $mutantReport);
    }

    public function testShouldPrepareSingleReportWithError()
    {
        $result = $this->createMutantResultStub('Fatal error: This is test error');

        $mutantReport = (new Text())->prepareReportForMutant($result);

        $expectedError =
            'The following output was received on stderr:' . PHP_EOL .
            PHP_EOL .
            'Fatal error: This is test error' . PHP_EOL .
            PHP_EOL;

        $this->assertEquals($this->getExpectedMutantReport() . $expectedError, $mutantReport);
    }

    public function testShouldPrepareAllMutantsReport()
    {
        $results = [
            $this->createMutantResult(),
            $this->createMutantResult(),
            $this->createMutantResult()
        ];

        $textReport = $this->getMock('Humbug\Report\Text', ['prepareReportForMutant']);

        $textReport->expects($this->at(0))->method('prepareReportForMutant')->with($results[0]);
        $textReport->expects($this->at(1))->method('prepareReportForMutant')->with($results[1]);
        $textReport->expects($this->at(2))->method('prepareReportForMutant')->with($results[2]);

        $mutantsReport = $textReport->prepareMutantsReport($results, 'Mutants');

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

    private function createMutantResultStub($errorOutput)
    {
        $mutant = $this->createMutant();

        $mutant->expects($this->once())->method('getMutation')->willReturn($this->mutation);
        $mutant->expects($this->once())->method('getDiff')->willReturn($this->diff);

        return new Result($mutant, Result::ESCAPE, '', $errorOutput);
    }

    private function createMutant()
    {
        return $this->getMock('Humbug\Mutant', [], [], '', false);
    }

    private function createMutantResult()
    {
        return $this->getMock('Humbug\TestSuite\Mutant\Result', [], [], '', false);
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
