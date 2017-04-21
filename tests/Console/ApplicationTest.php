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

namespace Humbug\Test\Console;

use Humbug\Console\Application;

/**
 * For actually running Humbug, refer to the Behat feature directory
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testApplicationHasHumbugLogoSet()
    {
        $app = new Application;
        $this->assertRegExp("/Humbug/", $app->getName());
    }
}
