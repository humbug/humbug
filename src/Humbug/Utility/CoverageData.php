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
use Humbug\Exception\NoCoveringTestsException;
use Humbug\Utility\TestTimeAnalyser;

class CoverageData
{

    protected $data;

    protected $analyser;

    public function __construct($file, TestTimeAnalyser $analyser)
    {
        $file = realpath($file);
        if (!file_exists($file)) {
            throw new InvalidArgumentException(
                'File does not exist: ' . $file
            );
        }
        $coverage = include $file;
        $this->data = $coverage->getData();
        $this->analyser = $analyser;
    }

    public function hasTestClasses($file, $line)
    {
        $file = realpath($file);
        if (!isset($this->data[$file])) {
            return false;
        }
        if (!isset($this->data[$file][$line])) {
            return false;
        }
        if (empty($this->data[$file][$line])) {
            return false;
        }
        return true;
    }

    public function getOrderedTestCases($file, $line)
    {
        $classes = $this->getTestClasses($file, $line);
        return $this->analyser->getTestCasesFromClasses($classes);
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
        foreach ($this->data[$file][$line] as $reference) {
            $parts = explode('::', $reference);
            $classes[] = $parts[0];
        }
        $classes = array_unique($classes);
        return $classes;
    }

}