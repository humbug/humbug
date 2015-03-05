<?php

namespace Humbug;

use Humbug\Utility\CoverageData;
use Symfony\Component\Process\PhpProcess;


/**
 * Process execution result
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */

class TestSuiteResult
{

    const SUCCESS = 0;

    const ERROR = 1;

    /**
     * @var int Exit code of the test suite.
     */
    private $exitCode = 0;

    /**
     * @var string Capture of the test suite's error output.
     */
    private $stdErr = '';

    /**
     * @var string Capture of the test suite's output.
     */
    private $stdOut = '';

    /**
     * @var int Status code of the test suite execution.
     */
    private $status = 0;

    /**
     * @var CoverageData
     */
    private $coverage = null;

    /**
     * @var float
     */
    private $lineCoverage = 0;

    /**
     * @param PhpProcess $process
     * @param Container $container
     * @param string $coverageFileName
     */
    public function __construct(PhpProcess $process, Container $container, $coverageFileName)
    {
        $this->exitCode =(int) $process->getExitCode();
        $this->stdOut = $process->getOutput();
        $this->stdErr = $process->getErrorOutput();

        $adapter = $container->getAdapter();

        if (! $adapter->ok($this->stdOut) || $process->getExitCode() !== 0) {
            $this->status = self::ERROR;
        }
        else {
            /**
             * Capture headline line coverage %.
             * Get code coverage data so we can determine which test suites or
             * or specifications need to be run for each mutation.
             */
            $this->coverage = $container->getAdapter()->getCoverageData($container);
            $this->lineCoverage = $this->coverage->getLineCoverageFrom(
                $container->getCacheDirectory() . $coverageFileName
            );
        }
    }

    /**
     * @return bool
     */
    public function isFailure()
    {
        return ($this->status != self::SUCCESS);
    }

    /**
     * @return CoverageData
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * @return float
     */
    public function getLineCoverage()
    {
        return $this->lineCoverage;
    }

    /**
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @return bool
     */
    public function hasStdErr()
    {
        return !empty($this->stdErr);
    }

    /**
     * @return string
     */
    public function getStdErr()
    {
        return $this->stdErr;
    }

    /**
     * @return bool
     */
    public function hasStdOut()
    {
        return !empty($this->stdOut);
    }

    /**
     * @return string
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }


}
