<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */

namespace Humbug\Test;

use Humbug\Mutant;
use Humbug\Exception\NoCoveringTestsException;
use Humbug\Mutation;
use Humbug\TestSuite\Mutant\FileGenerator;
use Humbug\Utility\CoverageData;
use Prophecy\Argument;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class MutantTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    private $coverage;

    private $tmp;

    private $mutation;

    /*public function setup()
    {
        $this->tmp = vfsStream::setup('tempDir');
        $this->file = vfsStream::url('tempDir/Foo.php');

        $this->mutation = new Mutation($this->file, 1, 'Foo', 'foo', 5, '\\Humbug\\Mutator\\Boolean\\True');

        file_put_contents($this->file, '<?php $foo = TRUE;');

        $this->container = m::mock('Humbug\\Container');
        $this->coverage = m::mock('Humbug\\Utility\\CoverageData');
        $process = m::mock('Symfony\\Component\\Process\\PhpProcess');

        $this->coverage
            ->shouldReceive('getTestClasses')
            ->with($this->mutation->getFile(), $this->mutation->getLine())
            ->andReturn(['FooTest.php']);
        $this->container
            ->shouldReceive('getTempDirectory')
            ->andReturn(vfsStream::url('tempDir'));
        $this->container
            ->shouldReceive('getBaseDirectory')
            ->andReturn(vfsStream::url('tempDir'));

        $process->shouldReceive('getOutput')->andReturn('foo1');
        $process->shouldReceive('getErrorOutput')->andReturn('foo2');
        $process->shouldReceive('stop'); //destructor
        $this->container
            ->shouldReceive('getAdapter->getProcess')
            ->with(m::any(), m::any(), m::any(), m::any(), m::any())
            ->andReturn($process);
    }

    public function testContruction()
    {
        $mutant = new Mutant($this->mutation, $this->container, $this->coverage);
        $this->assertTrue($mutant instanceof Mutant);

        $this->assertSame($this->mutation, $mutant->getMutation());
        $this->assertRegExp("/^vfs\:\/\/tempDir\/humbug\.mutant\.[0-9a-z]+\.php$/", $mutant->getFile());
        $this->assertSame(['FooTest.php'], $mutant->getTests());
    }

    public function testToArrayData()
    {
        $mutant = new Mutant($this->mutation, $this->container, $this->coverage);
        $expected = [
            'file' => 'Foo.php',
            'mutator' => '\\Humbug\\Mutator\\Boolean\\True',
            'class' => 'Foo',
            'method' => 'foo',
            'line' => 1,
            'diff' => "--- Original\n+++ New\n@@ @@\n-<?php \$foo = TRUE;\n+<?php \$foo = false;\n",
            'stdout' => 'foo1',
            'stderr' => 'foo2',
            'tests' => ['FooTest.php']
        ];
        $this->assertSame($expected, $mutant->toArray());
    }*/

    /**
     * @return FileGenerator
     */
    private function getFileGenerator()
    {
        $generator = $this->prophesize('Humbug\TestSuite\Mutant\FileGenerator');
        $generator->generateFile(Argument::type('Humbug\Mutation'))
            ->willReturn(__DIR__ . '/_files/mutants/mutant.pĥp');

        return $generator->reveal();
    }

    /**
     * @param array $tests
     * @param array $testMethods
     *
     * @return CoverageData
     */
    public function getCoverageData(array $tests = [], array $testMethods = [])
    {
        $coverageData = $this->prophesize('Humbug\Utility\CoverageData');

        $coverageData->getTestClasses(Argument::any(), Argument::any())
            ->willReturn($tests);
        $coverageData->getTestMethods(Argument::any(), Argument::any())
            ->willReturn($testMethods);

        return $coverageData->reveal();
    }

    /**
     * @param array $testMethods
     *
     * @return CoverageData
     */
    public function getExceptionRaisingCoverageData(array $testMethods = [])
    {
        $coverageData = $this->prophesize('Humbug\Utility\CoverageData');

        $coverageData->getTestClasses(Argument::any(), Argument::any())
            ->willThrow(new NoCoveringTestsException());
        $coverageData->getTestMethods(Argument::any(), Argument::any())
            ->willReturn($testMethods);

        return $coverageData->reveal();
    }

    public function getMutation()
    {
        return new Mutation(
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'mutables' . DIRECTORY_SEPARATOR . 'Math.php',
            8,
            'Phpunit_MM1_Math',
            'add',
            1,
            '\Humbug\Mutator\Arithmetic\Addition'
        );
    }

    public function testProperties()
    {
        $mutation = $this->getMutation();
        $mutant = new Mutant(
            $mutation,
            $this->getFileGenerator(),
            $this->getCoverageData([ 'dummy' ], [ 'dummyMethod' ]),
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'mutables' . DIRECTORY_SEPARATOR
        );

        $this->assertEquals($mutation, $mutant->getMutation());
        $this->assertEquals(__DIR__ . '/_files/mutants/mutant.pĥp', $mutant->getFile());
        $this->assertEquals(['dummy'], $mutant->getTests());
    }

    public function testConstructorReturnsEmptyTestsArrayWhenNoCoverage()
    {
        $mutant = new Mutant(
            $this->getMutation(),
            $this->getFileGenerator(),
            $this->getExceptionRaisingCoverageData(),
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'mutables' . DIRECTORY_SEPARATOR
        );
        $this->assertSame([], $mutant->getTests());
    }

    public function testToArray()
    {
        $mutation = $this->getMutation();
        $mutant = new Mutant(
            $mutation,
            new FileGenerator(__DIR__ . '/_files/mutants/'),
            $this->getCoverageData([ 'dummy' ], [ 'dummyMethod' ]),
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'mutables' . DIRECTORY_SEPARATOR
        );

        $diff = $this->prophesize('Humbug\Utility\Diff');
        $diff->difference(Argument::any(), Argument::any())
            ->willReturn('diff');

        $mutant->setDiffGenerator($diff->reveal());

        $expected = [
            'file' => 'Math.php',
            'mutator' => '\Humbug\Mutator\Arithmetic\Addition',
            'class' => 'Phpunit_MM1_Math',
            'method' => 'add',
            'line' => 8,
            'tests' => [ 'dummyMethod' ],
            'diff' => 'diff'
        ];

        $actual = $mutant->toArray();

        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $actual[$key]);
        }
    }
}
