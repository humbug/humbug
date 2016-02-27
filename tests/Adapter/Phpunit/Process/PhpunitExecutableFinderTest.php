<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test\Adapter\Phpunit\Process;

use Humbug\Adapter\Phpunit\Process\PhpunitExecutableFinder;
use org\bovigo\vfs\vfsStream;

class PhpunitExecutableFinderTest extends \PHPUnit_Framework_TestCase
{

    public function testFinderCanLocatePhpunitExecutable()
    {
        $finder = new PhpunitExecutableFinder(getcwd());
        $result = $finder->find();
        $this->assertRegExp('%phpunit(\\.bat|\\.phar)?$%', $result);
    }

    /**
     * @expectedException        \Humbug\Exception\RuntimeException
     * @expectedExceptionMessage Unable to locate a Composer executable on local system.
     */
    public function testFinderCannotLocateComposerShouldThrowException()
    {
        $root = vfsStream::setup('root', null, []);
        $finder = new PhpunitExecutableFinder($root->path());
        $finder->find();
    }
}
