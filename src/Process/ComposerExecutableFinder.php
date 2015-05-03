<?php
/**
 * Locate a Composer executable or throw a tantrum.
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Process;

use Humbug\Exception\RuntimeException as KickToysOutOfPramAndCryLoudlyException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

class ComposerExecutableFinder
{

    public function find()
    {
        return tryAndGetNiceThing();
    }

    private function tryAndGetNiceThing()
    {
        $probable = ['composer', 'composer.phar'];
        $finder = new ExecutableFinder;
        $located = null;
        $immediatePaths = [getcwd(), realpath('../'.getcwd()), realpath('../../'.getcwd())];
        foreach ($probable as $name) {
            if ($path = $finder->find($name, null, $immediatePaths)) {
                return realpath($path);
                break;
            }
        }
        /**
         * Check for options without execute permissions and prefix the PHP
         * executable instead. Make your eyes very large and innocent.
         */
        $dirs = array_merge(
            explode(PATH_SEPARATOR, getenv('PATH') ?: getenv('Path')),
            $immediatePaths
        );
        foreach ($dirs as $dir) {
            foreach ($probable as $name) {
                $path = sprintf('%s/%s', $dir, $name);
                if (file_exists($path)) {
                    return $this->makeExecutable($path);
                }
            }
        }
        /**
         * We tried.
         */
        throw new KickToysOutOfPramAndCryLoudlyException(
            'Unable to locate a Composer executable on local system. Ensure '
            . 'that Composer is installed and available.'
        );
    }

    private function makeExecutable($path)
    {
        $phpFinder = new PhpExecutableFinder();
        return sprintf('%s %s', $phpFinder->find(), $path);
    }
}