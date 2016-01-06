<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter;

use Humbug\Container;
use Symfony\Component\Process\Process;

abstract class AdapterAbstract
{
    
    /**
     * Runs the tests suite according to Runner set options and the execution
     * order of test case (if any). It then returns an array of two elements.
     * First element is a boolean result value indicating if tests passed or not.
     * Second element is an array containing the key "stdout" which stores the
     * output from the last test run.
     *
     * @param   Container         $container
     * @param   bool              $firstRun
     * @param   null|string       $interceptFile
     * @param   null|string       $mutantFile
     * @param   array             $testCases
     * @return  Process
     */
    abstract public function getProcess(
        Container $container,
        $firstRun = false,
        $interceptFile = null,
        $mutantFile = null,
        array $testCases = []
    );

    /**
     * Return adapter name
     *
     * @return string
     */
    abstract public function getName();

    abstract public function getClassFile($class, Container $container);

    /**
     * Parse the test adapter result output to see if there were any failures.
     * In the context of mutation testing, a test failure is good (i.e. the
     * mutation was detected by the test suite).
     *
     * This assumes the output is in Test Anywhere Protocol (TAP) format.
     *
     * @param string $output
     * @return bool True if the test passed, false if it failed
     */
    public function ok($output)
    {
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (preg_match("%not ok \\d+ - %", $line)
            && !preg_match("%# TODO%", $line)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Parse the test adapter result output and count ok results.
     *
     * This assumes the output is in Test Anywhere Protocol (TAP) format.
     *
     * TODO: Update regex to make certain we take the LAST numbered ok
     *
     * @param string $output
     * @return bool|int
     */
    public function hasOks($output)
    {
        $result = preg_match_all("%ok (\\d+) - .*%m", $output, $matches);
        if ($result) {
            return (int) end($matches[1]);
        }
        return false;
    }
}
