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
use Humbug\FUTException;
use Mockery as m;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->root = dirname(__FILE__) . '/_files/root/base1';
        $this->badRoot = '/path/does/not/exist';
    }

    public function teardown()
    {
        m::close();
    }

    public function testShouldStoreSourceDirectoryValue()
    {
        $generator = new Generator;
        $generator->setSourceDirectory($this->root . '/library');
        $this->assertEquals($this->root . '/library', $generator->getSourceDirectory());
    }

    /**
     * @expectedException \Humbug\FUTException
     */
    public function testShouldThrowExceptionOnNonexistingDirectory()
    {
        $generator = new Generator;
        $generator->setSourceDirectory($this->badRoot);
    }

    public function testShouldCollateAllFilesValidForMutationTesting()
    {
        $generator = new Generator;
        $generator->setSourceDirectory($this->root);
        $this->assertEquals(array(
            $this->root . '/library/bool2.php',
            $this->root . '/library/bool1.php'
        ),$generator->getFiles());
    }

    public function testShouldGenerateMutableFileObjects()
    {
        $generator = new Generator;
        $generator->setSourceDirectory($this->root);
        $mutable = m::mock('\\Humbug\\Mutable[generate]');
        $mutable->shouldReceive('setFilename');
        $mutable->shouldReceive('generate');
        $generator->generate($mutable);
        $mutables = $generator->getMutables();
        $this->assertTrue($mutables[0] instanceof Mutable);
    }

    public function testShouldGenerateAMutableFileObjectPerDetectedFile()
    {
        $generator = new Generator;
        $generator->setSourceDirectory($this->root);
        $mutable = $this->getMock('Mutable', array('generate', 'setFilename'));
        $generator->generate($mutable);
        $this->assertEquals(2, count($generator->getMutables()));
    }

}
