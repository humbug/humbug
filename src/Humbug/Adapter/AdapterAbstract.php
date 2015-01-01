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

abstract class AdapterAbstract
{

    const TIMED_OUT = 'timed out';

    /**
     * Output from the test library in use
     *
     * @var string
     */
    protected $output = '';
    
    /**
     * Runs the tests suite according to Runner set options and the execution
     * order of test case (if any). It then returns an array of two elements.
     * First element is a boolean result value indicating if tests passed or not.
     * Second element is an array containing the key "stdout" which stores the
     * output from the last test run.
     *
     * @param   \Humbug\container $container
     * @param   bool              $useStdout
     * @param   bool              $firstRun
     * @param   array             $mutation
     * @param   array             $testCases
     * @return  array
     */
    abstract public function runTests(Container $container, $useStdout = false,
    $firstRun = false, array $mutation = null, array $testCases = []);

    /**
     * Set the test library output so it can be used later
     *
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Get the test library output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }  
    
}
