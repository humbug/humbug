<?php

namespace Humbug\Test\Report;

use Humbug\Report\Text;

class TextTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldPrepareSingleMutationReport()
    {
        $diff = <<<DIFF
--- Original
+++ New
@@ @@
         }
-        return false;
+        return true;
     }
 }

DIFF;

        $mutation = [
            'mutator' => 'TestMutator',
            'class' => 'TestClass',
            'method' => 'testMethod',
            'file' => '/path/to/file'
        ];

        $mutant = $this->getMock('Humbug\Mutant', ['getMutation', 'getDiff'], [], '', false);

        $mutant->expects($this->once())->method('getMutation')->willReturn($mutation);
        $mutant->expects($this->once())->method('getDiff')->willReturn($diff);

        $mutantReport = (new Text())->prepareReportForMutant($mutant);

        $expectedReport =
            'TestMutator' . PHP_EOL .
            'Diff on TestClass::testMethod() in /path/to/file:' . PHP_EOL .
            $diff . PHP_EOL .
            PHP_EOL;

        $this->assertEquals($expectedReport, $mutantReport);
    }
}
