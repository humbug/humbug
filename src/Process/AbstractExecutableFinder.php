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

use Symfony\Component\Process\PhpExecutableFinder;

abstract class AbstractExecutableFinder
{

    /**
     * @return string
     */
    abstract public function find();

    /**
     * @param array $probableNames
     * @param array $extraDirectories
     * @return string|null
     */
    protected function searchNonExecutables(array $probableNames, array $extraDirectories = [])
    {
        $dirs = array_merge(
            explode(PATH_SEPARATOR, getenv('PATH') ?: getenv('Path')),
            $extraDirectories
        );
        foreach ($dirs as $dir) {
            foreach ($probableNames as $name) {
                $path = sprintf('%s/%s', $dir, $name);
                if (file_exists($path)) {
                    return $this->makeExecutable($path);
                }
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    protected function makeExecutable($path)
    {
        $phpFinder = new PhpExecutableFinder();
        return sprintf('%s %s', $phpFinder->find(), $path);
    }
}
