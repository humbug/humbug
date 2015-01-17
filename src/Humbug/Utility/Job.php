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
    public static function generate($mutantFile = null, array $args = [], $bootstrap = '', $replacingFile = null)
    {
        $humbugBootstrap = realpath(__DIR__ . '/../../bootstrap.php');
        $file = sys_get_temp_dir() . '/humbug.phpunit.bootstrap.php';

        if (!is_null($mutantFile)) {
            $mutantFile = addslashes($mutantFile);
            $replacingFile = addslashes($replacingFile);
            $prepend = <<<PREPEND
<?php
require_once "{$humbugBootstrap}";
use Humbug\StreamWrapper\IncludeInterceptor;
IncludeInterceptor::intercept("{$replacingFile}", "{$mutantFile}");
IncludeInterceptor::enable();
PREPEND;
            if (!empty($bootstrap)) {
                $buffer = $prepend . "\nrequire_once '{$bootstrap}';";
            } else {
                $buffer = $prepend;
            }
            file_put_contents($file, $buffer);
        } else {
            if (!empty($bootstrap)) {
                $buffer = "<?php\nrequire_once '{$bootstrap}';";
            } else {
                $buffer = "<?php\n";
            }
            file_put_contents($file, $buffer);
        }

        $args = base64_encode(serialize($args));
        
        $script = <<<SCRIPT
<?php
namespace Humbug\\Env;
require_once "{$humbugBootstrap}";
error_reporting(error_reporting() & ~E_NOTICE);
use Humbug\Adapter\Phpunit;
Phpunit::main("{$args}");
SCRIPT;
        return $script;
    }
}
