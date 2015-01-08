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
        $args = base64_encode(serialize($args));
        $humbugBootstrap = realpath(__DIR__ . '/../../bootstrap.php');
        $bootstrap = addslashes($bootstrap);
        $inBeforeAutoloader = '';
        if (!is_null($mutantFile)) {
            $mutantFile = addslashes($mutantFile);
            $replacingFile = addslashes($replacingFile);
            $inBeforeAutoloader = <<<PREPEND
use Humbug\StreamWrapper\IncludeInterceptor;
IncludeInterceptor::intercept("{$replacingFile}", "{$mutantFile}");
IncludeInterceptor::enable();
PREPEND;
        }
        $script = <<<SCRIPT
<?php
namespace Humbug\\Env;
error_reporting(error_reporting() & ~E_NOTICE);
require_once "{$humbugBootstrap}";
{$inBeforeAutoloader}
\$bootstrap = "{$bootstrap}";
if (!empty(\$bootstrap)) require_once "{$bootstrap}";
use Humbug\Adapter\Phpunit;
Phpunit::main("{$args}");
SCRIPT;
        return $script;
    }
    
}
