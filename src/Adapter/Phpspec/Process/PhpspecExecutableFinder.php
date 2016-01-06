<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter\Phpspec\Process;

use Humbug\Process\AbstractExecutableFinder;
use Humbug\Exception\RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

class PhpspecExecutableFinder extends AbstractExecutableFinder
{

    /**
     * @return string
     */
    public function find()
    {
        $this->checkVendorPath();
        return $this->findPhpspec();
    }

    /**
     * @return string
     */
    private function findPhpspec()
    {
        $probable = ['phpspec', 'phpspec.phar'];
        $finder = new ExecutableFinder;
        foreach ($probable as $name) {
            if ($path = $finder->find($name, null, [getcwd()])) {
                return $this->makeExecutable($path);
            }
        }
        $result = $this->searchNonExecutables($probable, [getcwd()]);
        if (!is_null($result)) {
            return $result;
        }
        throw new RuntimeException(
            'Unable to locate an Phpspec executable on local system. Ensure '
            . 'that Phpspec is installed and available.'
        );
    }

    /**
     * Prefix commands with exec outside Windows to ensure process timeouts
     * are enforced and end PHP processes properly
     *
     * @param string $path
     * @return string
     */
    protected function makeExecutable($path)
    {
        $path = realpath($path);
        $phpFinder = new PhpExecutableFinder();
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            return sprintf('%s %s %s', 'exec', $phpFinder->find(), $path);
        } else {
            if (false !== strpos($path, '.bat')) {
                return $path;
            }
            return sprintf('%s %s', $phpFinder->find(), $path);
        }
    }
}
