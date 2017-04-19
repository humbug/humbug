<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test\Process;

use Humbug\Process\ComposerExecutableFinder;
use org\bovigo\vfs\vfsStream;

class ComposerExecutableFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFinderCanLocatePhpunitExecutable()
    {
        $finder = new ComposerExecutableFinder(getcwd());
        $result = $finder->find();
        $this->assertRegExp('%composer(\\.bat|\\.phar)?$%', $result);
    }

    /**
     * @expectedException        \Humbug\Exception\RuntimeException
     * @expectedExceptionMessage Unable to locate a Composer executable on local system.
     */
    public function testFinderCannotLocateComposerExecutableShouldThrowException()
    {
        $root = vfsStream::setup('root');
        $finder = new ComposerExecutableFinder($root->path());
        $finder->find();
    }
}
