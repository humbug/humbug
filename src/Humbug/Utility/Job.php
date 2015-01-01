<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Utility;

class Job
{
    
    /**
     * Generate a new Job script to be executed under a separate PHP process
     *
     * @param array $mutation Mutation data and objects to be used
     * @return string
     */
    public static function generate(array $mutation = null, array $args = [], $bootstrap = '', $timeout = 120)
    {
        $args = base64_encode(serialize($args));
        if (!is_null($mutation)) {
            $mutation = '"'.base64_encode(serialize($mutation)).'"';
        } else {
            $mutation = 'null';
        }
        $humbug = realpath(__DIR__ . '/../../bootstrap.php');
        $bootstrap = addslashes($bootstrap);
        $script = <<<SCRIPT
<?php
namespace Humbug\\Env;
error_reporting(error_reporting() & ~E_NOTICE);
if (!empty("{$bootstrap}")) require_once "{$bootstrap}";
require_once "{$humbug}";
use Humbug\Adapter\Phpunit;
class Job {
    static function main () {
        Phpunit::main(
            "{$args}",
            {$mutation}
        );
    }
}
Job::main();
SCRIPT;
        return $script;
    }
    
}
