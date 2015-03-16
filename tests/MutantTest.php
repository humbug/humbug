<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test;

use Humbug\Mutant;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class MutantTest extends \PHPUnit_Framework_TestCase
{

    private $container;

    private $coverage;

    private $tmp;

    private $mutation = [
        'line' => 1,
        'index' => 5,
        'mutator' => '\\Humbug\\Mutator\\Boolean\\True',
        'class' => 'Foo',
        'method' => 'foo'
    ];

    public function setup()
    {
        $this->tmp = vfsStream::setup('tempDir');
        $this->file = vfsStream::url('tempDir/Foo.php');
        $this->mutation['file'] = $this->file;
        file_put_contents($this->file, '<?php $foo = TRUE;');

        $this->container = m::mock('Humbug\\Container');
        $this->coverage = m::mock('Humbug\\Utility\\CoverageData');
        $process = m::mock('Symfony\\Component\\Process\\PhpProcess');

        $this->coverage
            ->shouldReceive('getTestClasses')
            ->with($this->mutation['file'], $this->mutation['line'])
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
    }
}
