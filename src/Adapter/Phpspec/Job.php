<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter\Phpspec;

class Job
{
    /**
     * Generate a new Job script to be executed under a separate PHP process
     *
     * @param null|string   $mutantFile
     * @param array         $args
     * @param string        $bootstrap
     * @param null|string   $replacingFile
     * @return string
     */
    public static function generate($mutantFile = null, $bootstrap = '', $replacingFile = null)
    {
        if ('phar:' === substr(__FILE__, 0, 5)) {
            $humbugBootstrap = \Phar::running() . '/bootstrap.php';
        } else {
            $humbugBootstrap = realpath(__DIR__ . '/../../../bootstrap.php');
        }

        $file = sys_get_temp_dir() . '/humbug.phpspec.bootstrap.php';

        $workaround = <<<WORKAROUND
/**
 * Workaround for PhpSpec\Console\ContainerAssembler depending on this
 * though it won't exist in this new process.
 */
if (!isset(\$_SERVER['HOME'])) {
    \$_SERVER['HOME'] = sys_get_temp_dir();
}\n
WORKAROUND;

        if (!is_null($mutantFile)) {
            $mutantFile = addslashes($mutantFile);
            $replacingFile = addslashes($replacingFile);
            $prepend = <<<PREPEND
<?php
require_once '{$humbugBootstrap}';
use Humbug\StreamWrapper\IncludeInterceptor;
IncludeInterceptor::intercept('{$replacingFile}', '{$mutantFile}');
IncludeInterceptor::enable();
PREPEND;
            if (!empty($bootstrap)) {
                $buffer = $workaround . $prepend . "\nrequire_once '{$bootstrap}';";
            } else {
                $buffer = $workaround . $prepend;
            }
            file_put_contents($file, $buffer);
        } else {
            if (!empty($bootstrap)) {
                $buffer = $workaround . "<?php\nrequire_once '{$humbugBootstrap}';\nrequire_once '{$bootstrap}';";
            } else {
                $buffer = $workaround . "<?php\nrequire_once '{$humbugBootstrap}';";
            }
            file_put_contents($file, $buffer);
        }
}
