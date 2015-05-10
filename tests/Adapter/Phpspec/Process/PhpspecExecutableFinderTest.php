<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test\Adapter\Phpspec\Process;

use Humbug\Adapter\Phpspec\Process\PhpspecExecutableFinder;

class PhpspecExecutableFinderTest extends \PHPUnit_Framework_TestCase
{

    public function testFinderCanLocatePhpspecExecutable()
    {
        $finder = new PhpspecExecutableFinder();
        $result = $finder->find();
        $this->assertRegExp('%phpspec(\\.bat|\\.phar)?$%', $result);
    }
}
