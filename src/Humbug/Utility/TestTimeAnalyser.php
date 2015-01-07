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

class TestTimeAnalyser
{

    protected $log = null;

    protected $testCases;

    protected $testSuites;

    public function __construct($logFile)
    {
        if (!file_exists($logFile) || !is_readable($logFile)) {
            throw new InvalidArgumentException(
                'The JUnit log file was not found or was not readable: '
                . $logFile
            );
        }
        $this->log = file_get_contents($logFile);
    }

    public function process()
    {
        $testCases = [];
        $testSuites = [];
        $time = [];
        $dom = new \DOMDocument;
        $dom->loadXML($this->log);
        $xpath = new \DOMXPath($dom);

        /**
         * Get test suites and keep a record of test classes and files
         */
        $elements = $xpath->query('//testsuite');
        foreach ($elements as $suite) {
            if (!$suite->hasAttribute('name') || !$suite->hasAttribute('file')) {
                continue;
            }
            $testSuites[$suite->getAttribute('name')] = $suite->getAttribute('file');
        }
        $this->testSuites = $testSuites;

        /**
         * Get test cases and order them by time performance
         */
        $elements = $xpath->query('//testcase');
        foreach ($elements as $case) {
            // This may exclude data provision run data
            if ($case->hasAttribute('class')) { 
                if (!isset($testCases[$case->getAttribute('class')])) {
                    $testCases[$case->getAttribute('class')] = [
                        'class' => $case->getAttribute('class'),
                        'file' => $case->getAttribute('file'),
                        'time' => 0
                    ];
                }
                $testCases[$case->getAttribute('class')]['time'] += (float) $case->getAttribute('time');
            }
        }
        unset($xpath);
        foreach ($testCases as $key => $value) {
            $time[$key] = $value['time'];
        }
        array_multisort($time, SORT_ASC, $testCases);
        $this->testCases = $testCases;
        return $this;
    }

    public function getTestCases()
    {
        return $this->testCases;
    }

    public function getTestSuiteFile($suiteClass)
    {
        if (!isset($this->testSuites[$suiteClass])) {
            throw new InvalidArgumentException(
                $suiteClass . ' does not exist on previously generated JUnit log'
            );
        }
        return $this->testSuites[$suiteClass];
    }

    public function getTestCasesFromClasses(array $suiteClasses)
    {
        foreach ($suiteClasses as $suiteClass) {
            if (!isset($this->testSuites[$suiteClass])) {
                throw new InvalidArgumentException(
                    $suiteClass . ' does not exist on previously generated JUnit log'
                );
            }
        }
        $cases = [];
        // put faster test classes first for a little extra speed
        foreach ($this->testCases as $class => $case) {
            foreach ($suiteClasses as $suite) {
                if ($class == $suite) {
                    $cases[] = $case;
                    break;
                }
            }
        }
        return $cases;
    }
    
}
