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

class PhpunitExecutableFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testFinderCanLocatePhpunitExecutable()
    {
        $finder = new PhpunitExecutableFinder();
        $result = $finder->find();
        $this->assertRegExp('%phpunit.*(\\.bat|\\.phar)?$%i', $result);
    }

    public function testFinderCanLocateACustomPhpunitNotInVendorOrPath()
    {
        $this->markTestIncomplete('Design test to inject custom config for this.');
    }
}
