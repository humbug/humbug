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
    /**
     * @var string
     */
    private $searchDir;

    /**
     * @var Finder
     */
    private $finder;

    protected function setUp()
    {
        $this->searchDir = dirname(__FILE__) . '/_files/root/base1';

        $this->finder = $this->createPhpFileFinder($this->searchDir);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testShouldCollateAllFilesValidForMutationTesting()
    {
        $this->finder->sortByName();

        $generator = new Generator;
        $generator->generate($this->finder);
        $mutables = $generator->getMutables();

        $this->assertEquals($mutables[0]->getFilename(), $this->searchDir . '/library/bool1.php');
        $this->assertEquals($mutables[1]->getFilename(), $this->searchDir . '/library/bool2.php');
    }

    public function testShouldGenerateMutableFileObjects()
    {
        $generator = new Generator;
        $mutable = m::mock('\\Humbug\\Mutable[]');

        $generator->generate($this->finder, $mutable);
        $mutables = $generator->getMutables();

        $this->assertTrue($mutables[0] instanceof Mutable);
    }

    public function testShouldGenerateAMutableFileObjectPerDetectedFile()
    {
        $generator = new Generator;

        $mutable = $this->getMock('Mutable', ['setFilename']);

        $generator->generate($this->finder, $mutable);
        $this->assertEquals(2, count($generator->getMutables()));
    }

    private function createPhpFileFinder($searchDir)
    {
        $finder = new Finder;
        $finder->files()->name('*.php');
        $finder->in($searchDir);

        return $finder;
    }
}
