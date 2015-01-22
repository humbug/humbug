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

namespace Humbug\Test\Utility;

use Humbug\Utility\Job;

class JobTest extends \PHPUnit_Framework_TestCase
{

    public function testGenerateReturnsPHPScriptRenderedWithCurrentRunnersSettingsAndSerialisedMutationArray()
    {
        $script = Job::generate('the_file.php', [], '/path/to/bootstrap.php');
        $bootstrap = realpath(__DIR__ . '/../../../../src/bootstrap.php');
        $expected = <<<EXPECTED
<?php
namespace Humbug\\Env;
require_once '{$bootstrap}';
error_reporting(error_reporting() & ~E_NOTICE);
use Humbug\Adapter\Phpunit;
Phpunit::main('YTowOnt9');
EXPECTED;
        $this->assertEquals($expected, $script);
    }
}
