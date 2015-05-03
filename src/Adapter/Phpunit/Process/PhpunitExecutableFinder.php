<?php
/**
 * Locate a Composer executable or throw a tantrum.
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter\Phpunit\Process;

use Humbug\Process\ComposerExecutableFinder;
use Humbug\Exception\RuntimeException;
use Symfony\Component\Process\Process;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

class PhpunitExecutableFinder
{

    public function find()
    {
        $this->checkVendorPath();
        return $this->findPhpunit()
    }

    private function checkVendorPath()
    {
        $vendorPath = null;
        try {
            $composer = $this->findComposer();
            $process = new Process(sprintf('%s %s', $composer, 'config bin-dir'));
            $process->run();
            $vendorPath = trim($process->getOutput());
        } catch (RuntimeException $e) {
            $candidate = getcwd() . '/vendor/bin';
            if (file_exists($candidate)) {
                $vendorPath = $candidate;
            }
        }
        if (!is_null($vendorPath)) {
            putenv('PATH=' . $vendorPath . PATH_SEPARATOR . getenv('PATH'));
        }
    }

    private function findComposer()
    {
        $finder = new ComposerExecutableFinder;
        return $finder->find();
    }

    private function findPhpunit()
    {
        $probable = ['phpunit', 'phpunit.phar'];
        $finder = new ExecutableFinder;
        $located = null;
        foreach ($probable as $name) {
            if ($path = $finder->find($name, null, [getcwd()])) {
                return realpath($path);
            }
        }
        $dirs = array_merge(
            explode(PATH_SEPARATOR, getenv('PATH') ?: getenv('Path')),
            [getcwd()]
        );
        foreach ($dirs as $dir) {
            foreach ($probable as $name) {
                $path = sprintf('%s/%s', $dir, $name);
                if (file_exists($path)) {
                    return $this->makeExecutable($path);
                }
            }
        }
        throw new RuntimeException(
            'Unable to locate a PHPUnit executable on local system. Ensure '
            . 'that PHPUnit is installed and available.'
        );
    }

    private function makeExecutable($path)
    {
        $phpFinder = new PhpExecutableFinder();
        return sprintf('%s %s', $phpFinder->find(), $path);
    }
}