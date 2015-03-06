<?php

/**
 * Process execution result
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Unit;

use Humbug\Utility\CoverageData;
use Symfony\Component\Process\PhpProcess;

class Result
{

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
     * @var bool Whether a failure was detected during execution of the test suite.
     */
    private $hasFailure = 0;

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
     * @param bool $hasFailure
     * @param CoverageData $coverage
     * @param float $lineCoverage
     */
    public function __construct(PhpProcess $process, $hasFailure, CoverageData $coverage = null, $lineCoverage)
    {
        $this->hasFailure = (bool)$hasFailure;
        $this->exitCode = (int)$process->getExitCode();
        $this->stdOut = $process->getOutput();
        $this->stdErr = $process->getErrorOutput();

        $this->coverage = $coverage;
        $this->lineCoverage = $lineCoverage;
    }

    /**
     * @return bool
     */
    public function hasFailure()
    {
        return $this->hasFailure;
    }

    /**
     * @return bool
     */
    public function hasCoverage()
    {
        return ($this->coverage != null);
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

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return (!$this->hasFailure() && $this->getExitCode() == 0);
    }
}
