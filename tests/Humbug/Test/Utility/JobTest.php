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
        $script = Job::generate(['a', '1', new \stdClass], [], '/path/to/bootstrap.php');
        $bootstrap = realpath(__DIR__ . '/../../../../src/bootstrap.php');
        $expected = <<<EXPECTED
<?php
namespace Humbug\\Env;
declare(ticks = 1);
error_reporting(error_reporting() & ~E_NOTICE);
if (!empty("/path/to/bootstrap.php")) require_once "/path/to/bootstrap.php";
require_once "{$bootstrap}";
use Humbug\Adapter\Phpunit;
class Job {
    static function main () {
        Phpunit::main(
            "YTowOnt9",
            "YTozOntpOjA7czoxOiJhIjtpOjE7czoxOiIxIjtpOjI7Tzo4OiJzdGRDbGFzcyI6MDp7fX0="
        );
    }
    static function timeout() {
        throw new \\Exception('Timed Out');
    }
}
pcntl_signal(SIGALRM, array('\\Humbug\\Env\\Job', 'timeout'), TRUE);
pcntl_alarm(120);
try {
    Job::main();
} catch (\\Exception \$e) {
    pcntl_alarm(0);
    throw \$e;
}
pcntl_alarm(0);
EXPECTED;
        $this->assertEquals($expected, $script);
    }
   
}
