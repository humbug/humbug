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

use Humbug\Exception\InvalidArgumentException;
use Humbug\Exception\RuntimeException;
use Humbug\Exception\NoCoveringTestsException;
use Symfony\Component\Finder\Finder;

class CoverageData
{

    protected $data;

    protected $lastFile;

    /**
     * The constructor processes the main coverage report into
     * a set of split files. A coverage data extract per source code file
     * available.
     */
    public function __construct($file)
    {
        if (!$path = realpath($file)) {
            throw new InvalidArgumentException(
                'File does not exist: ' . $file
            );
        }
        $this->process($path);
    }

    public function loadCoverageFor($file)
    {
        if ($file === $this->lastFile) {
            return;
        }

        $this->lastFile = $file;

        unset($this->data);
        gc_collect_cycles();
        $cache = sys_get_temp_dir() . '/coverage.humbug.' . md5($file) . '.cache';
        if (!file_exists($cache)) {
            throw new NoCoveringTestsException(
                'No coverage data for this file could be located:' . $file
            );
        }
        $coverage = include $cache;
        $this->data = $coverage->getData();

        unset($coverage);
    }

    public function hasTestClasses($file, $line)
    {
        $file = realpath($file);

        if (!isset($this->data[$file])) {
            return false;
        } elseif (!isset($this->data[$file][$line])) {
            return false;
        } elseif (empty($this->data[$file][$line])) {
            return false;
        }
        return true;
    }

    public function getTestClasses($file, $line)
    {
        $file = realpath($file);
        if (!$this->hasTestClasses($file, $line)) {
            throw new NoCoveringTestsException(
                'Line '.$line.' of '.$file.' has no associated test classes per '
                . 'the coverage report'
            );
        }
        $classes = [];
        $cases = [];
        $line = $this->data[$file][$line];
        foreach ($line as $reference) {
            $parts = explode('::', $reference);
            $classes[] = $parts[0];
            $caseParts = explode(' ', $parts[1]);
            $cases[] = $caseParts[0];
        }
        unset($line);
        $classes = array_unique($classes);
        return $classes;
    }

    public function getTestMethods($file, $line)
    {
        $file = realpath($file);

        if (!$this->hasTestClasses($file, $line)) {
            throw new NoCoveringTestsException(
                'Line '.$line.' of '.$file.' has no associated test classes per '
                . 'the coverage report'
            );
        }

        return $this->data[$file][$line];
    }
    
    public function getLineCoverageFrom($file)
    {
        $file = realpath($file);
        if (!file_exists($file)) {
            throw new InvalidArgumentException(
                'File does not exist containing text code coverage: ' . $file
            );
        }
        $text = file_get_contents($file);
        if (preg_match("/Lines\\:[\s]*([\\d\\.]+)%/", $text, $matches)) {
            return (float) $matches[1];
        }
        throw new RuntimeException(
            'Text code coverage file could not be parsed from: ' . $file
        );
    }

    public function cleanup()
    {
        $finder = new Finder;
        $finder->files()->ignoreUnreadableDirs()->name('coverage.humbug.*.cache');
        foreach ($finder->in(sys_get_temp_dir()) as $file) {
            @unlink($file->getRealpath());
        }
    }

    protected function process($file)
    {
        $fp = fopen($file, 'r');
        $start = false;
        $passthru = false;
        $out = null;
        $matches = null;
        while (false !== ($line = fgets($fp))) {
            if ($passthru === true && !preg_match("%^  '[^']*' => $%", $line)) {
                if (preg_match("%^\\)\\)\\;%", $line)) {
                    $this->wrapup($out);
                    break;
                } else {
                    fwrite($out, $line);
                    continue;
                }
            }
            if ($start === true && preg_match("%^  '([^']*)' => $%", $line, $matches)) {
                if ($passthru === true) {
                    $this->wrapup($out);
                }
                $file = 'coverage.humbug.' . md5(stripslashes($matches[1])) . '.cache';
                $out = fopen(sys_get_temp_dir() . '/' . $file, 'w');
                $buffer = '<?php'
                    . PHP_EOL . '$coverage = new PHP_CodeCoverage;'
                    . PHP_EOL . '$coverage->setData(array ('
                    . PHP_EOL . '  \'' . $matches[1] . '\' => '
                    . PHP_EOL;
                fwrite($out, $buffer);
                $passthru = true;
                continue;
            }
            if ($start === false && preg_match("%^\\\$coverage\\-\\>setData%", $line)) {
                $start = true;
                continue;
            }
        }
        fclose($fp);
    }

    protected function wrapup($out)
    {
        $buffer = PHP_EOL . '));'
            . PHP_EOL . 'return $coverage;';
        fwrite($out, $buffer);
        fclose($out);
    }
}
