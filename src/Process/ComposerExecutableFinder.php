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

class ComposerExecutableFinder extends AbstractExecutableFinder
{

    /**
     * @return string
     */
    public function find()
    {
        return $this->tryAndGetNiceThing();
    }

    /**
     * @return string
     */
    private function tryAndGetNiceThing()
    {
        $probable = ['composer', 'composer.phar'];
        $finder = new ExecutableFinder;
        $located = null;
        $immediatePaths = [getcwd(), realpath(getcwd().'/../'), realpath(getcwd().'/../../')];
        foreach ($probable as $name) {
            if ($path = $finder->find($name, null, $immediatePaths)) {
                return $path;
                break;
            }
        }
        /**
         * Check for options without execute permissions and prefix the PHP
         * executable instead. Make your eyes very large and innocent.
         */
        $result = $this->searchNonExecutables($probable, $immediatePaths);
        if (!is_null($result)) {
            return $result;
        }
        /**
         * We tried.
         */
        throw new KickToysOutOfPramAndCryLoudlyException(
            'Unable to locate a Composer executable on local system. Ensure '
            . 'that Composer is installed and available.'
        );
    }
}
