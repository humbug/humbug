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

class GeneratorTest extends \PHPUnit\Framework\TestCase
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
        $this->searchDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'root' . DIRECTORY_SEPARATOR . 'base1';

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

        $this->assertEquals($mutables[0]->getFilename(), $this->searchDir . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'bool1.php');
        $this->assertEquals($mutables[1]->getFilename(), $this->searchDir . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'bool2.php');
    }

    private function createPhpFileFinder($searchDir)
    {
        $finder = new Finder;
        $finder->files()->name('*.php');
        $finder->in($searchDir);

        return $finder;
    }
}
