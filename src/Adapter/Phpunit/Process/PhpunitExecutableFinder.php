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

use Humbug\Process\AbstractExecutableFinder;
use Humbug\Process\ComposerExecutableFinder;
use Humbug\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

class PhpunitExecutableFinder extends AbstractExecutableFinder
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function find()
    {
        $this->checkVendorPath();
        return $this->findPhpunit();
    }

    /**
     * @return void
     */
    private function checkVendorPath()
    {
        $vendorPath = null;
        $composer = $this->findComposer();
        try {
            $process = new Process(sprintf('%s %s', $composer, 'config bin-dir'));
            $process->run();
            $vendorPath = trim($process->getOutput());
        } catch (RuntimeException $e) {
            $candidate = $this->basePath . '/vendor/bin';
            if (file_exists($candidate)) {
                $vendorPath = $candidate;
            }
        }
        if (!is_null($vendorPath)) {
            putenv('PATH=' . $vendorPath . PATH_SEPARATOR . getenv('PATH'));
        }
    }

    /**
     * @return string
     */
    private function findComposer()
    {
        $finder = new ComposerExecutableFinder($this->basePath);
        return $finder->find();
    }

    /**
     * @return string
     */
    private function findPhpunit()
    {
        $probable = ['phpunit', 'phpunit.phar'];
        $finder = new ExecutableFinder;
        foreach ($probable as $name) {
            if ($path = $finder->find($name, null, [$this->basePath])) {
                return $this->makeExecutable($path);
            }
        }
        $result = $this->searchNonExecutables($probable, [$this->basePath]);
        if (!is_null($result)) {
            return $result;
        }
        throw new RuntimeException(
            'Unable to locate a PHPUnit executable on local system. Ensure '
            . 'that PHPUnit is installed and available.'
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
