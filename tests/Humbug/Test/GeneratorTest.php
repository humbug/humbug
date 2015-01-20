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

use Humbug\Generator;
use Humbug\Mutable;
use Mockery as m;
use Symfony\Component\Finder\Finder;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $searchDir;

    protected function setUp()
    {
        $this->searchDir = dirname(__FILE__) . '/_files/root/base1';
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testShouldCollateAllFilesValidForMutationTesting()
    {
        $finder = new Finder;
        $finder->files()->name('*.php');
        $finder->in($this->searchDir);
        $finder->sortByName();

        $generator = new Generator;
        $generator->generate($finder);
        $mutables = $generator->getMutables();

        $this->assertEquals($mutables[0]->getFilename(), $this->searchDir . '/library/bool1.php');
        $this->assertEquals($mutables[1]->getFilename(), $this->searchDir . '/library/bool2.php');
    }

    public function testShouldGenerateMutableFileObjects()
    {
        $finder = new Finder;
        $finder->files()->name('*.php');
        $finder->in($this->searchDir);

        $generator = new Generator;
        $mutable = m::mock('\\Humbug\\Mutable[generate]');
        $mutable->shouldReceive('setFilename');
        $mutable->shouldReceive('generate');
        $generator->generate($finder, $mutable);
        $mutables = $generator->getMutables();
        $this->assertTrue($mutables[0] instanceof Mutable);
    }

    public function testShouldGenerateAMutableFileObjectPerDetectedFile()
    {
        $finder = new Finder;
        $finder->files()->name('*.php');
        $finder->in($this->searchDir);

        $generator = new Generator;
        $mutable = $this->getMock('Mutable', ['generate', 'setFilename']);
        $generator->generate($finder, $mutable);
        $this->assertEquals(2, count($generator->getMutables()));
    }

}
