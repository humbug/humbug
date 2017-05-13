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
use Humbug\Config;
use Humbug\Config\JsonParser;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

class PhpunitExecutableFinder extends AbstractExecutableFinder
{

    /**
     * @var Config
     */
    private $config;

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

    /**
     * @return string
     */
    private function findComposer()
    {
        $finder = new ComposerExecutableFinder;
        return $finder->find();
    }

    /**
     * @return string
     */
    private function findPhpunit()
    {
        $probable = $this->getExecutableNames();
        $dir = $this->getPhpunitExecutablePath();
        $finder = new ExecutableFinder;
        foreach ($probable as $name) {
            if ($path = $finder->find($name, null, [$dir])) {
                return $this->makeExecutable($path);
            }
        }
        $result = $this->searchNonExecutables($probable, [$dir]);
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
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            return sprintf('%s %s', 'exec', $path);
        }
        return $path;
    }

    private function setConfig()
    {
        $config = (new JsonParser())->parseFile();
        $this->config = new Config($config);
    }

    /**
     * @return Config
     */
    private function getConfig()
    {
        if (is_null($this->config)) {
            $this->setConfig();
        }
        return $this->config;
    }

    /**
     * @return array
     */
    private function getExecutableNames()
    {
        if ($this->getConfig()->isPhpunitConfigured()) {
            return [basename($this->getConfig()->getPhpunitConfig()->phar)];
        }
        return ['phpunit', 'phpunit.phar'];
    }

    /**
     * @return string
     */
    private function getPhpunitExecutablePath()
    {
        if ($this->getConfig()->isPhpunitConfigured()) {
            return dirname($this->getConfig()->getPhpunitConfig()->phar);
        }
        return getcwd();
    }
}
