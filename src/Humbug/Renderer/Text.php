<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Renderer;

use Symfony\Component\Console\Output\OutputInterface;

class Text
{

    protected $output;

    protected $progressCount = 0;

    protected $type = OutputInterface::OUTPUT_NORMAL;

    protected $useBuffer;

    protected $buffer;

    public function __construct(OutputInterface $output, $buffer = false, $colors = true)
    {
        $this->output = $output;
        if (!$colors) {
            $this->type = OutputInterface::OUTPUT_PLAIN;
        }
        $this->useBuffer = $buffer;
    }

    public function write($string, $eol = true)
    {
        if ($eol) {
            $this->output->writeln($string, $this->type);
        } else {
            $this->output->write($string, false, $this->type);
        }
        if ($this->useBuffer) {
            $this->buffer .= strip_tags($string) . ($eol? PHP_EOL : '');
        }
    }

    public function getBuffer()
    {
        $return = $this->buffer;
        $this->buffer = '';
        return $return;
    }

    /**
     * Render message preceeding the initial test run
     *
     * @param array $result
     */
    public function renderPreTestIntroduction()
    {
        $this->write('Humbug running test suite to generate logs and code coverage data...');
    }

    /**
     * Render message where the initial test run didn't pass (excl. incomplete/skipped/risky tests)
     *
     * @param array $result
     */
    public function renderInitialRunFail(array $result)
    {
        $this->write('<warning>Tests must be in a fully passing state before Humbug is run.</warning>');
        $this->write('<warning>Incomplete, skipped or risky tests are allowed.</warning>');
        if (!empty($result['stdout'])) {
            $this->write('<warning>Stdout: \n' . $this->indent($result['stdout']) . '\n</warning>');
        }
        if (!empty($result['stderr'])) {
            $this->write('<warning>Stderr: \n' . $this->indent($result['stderr']) . '\n</warning>');
        }
    }

    /**
     * Render message where the initial test run didn't pass (excl. incomplete/skipped/risky tests)
     *
     * @param array $result
     */
    public function renderInitialRunPass(array $result)
    {
        $this->write('Humbug has completed the initial test run successfully.');
    }

    /**
     * Render message that Humbug is analysing files.
     *
     * @param array $result
     */
    public function renderStaticAnalysisStart()
    {
        $this->write('Humbug is analysing source files...');
    }

    /**
     * Render message that mutation testing loop is starting
     *
     * @param array $result
     */
    public function renderMutationTestingStart()
    {
        $this->write('Mutation Testing is commencing...');
        $this->write(
            '(<options=bold>.</options=bold>: killed, '
            . '<fg=red;options=bold>M</fg=red;options=bold>: escaped, '
            . '<fg=blue;options=bold>S</fg=blue;options=bold>: uncovered, '
            . '<fg=yellow;options=bold>E</fg=yellow;options=bold>: fatal error, '
            . '<fg=cyan;options=bold>T</fg=cyan;options=bold>: timed out)'
        );
    }

    /**
     * Render a progress marker:
     *  T: The test run timed out, possibly due to an infinite loop or underestimated timeout
     *  E: The test run hit a fatal error, either kicked out from a test or due to a Humbug issue
     *  M: The test run was successful. The mutation went undetected by the unit tests.
     *  .: The test run included a fail condition. The mutation was detected!!!
     *
     * @param array $result
     */
    public function renderProgressMark(array $result, $eolInterval = 60)
    {
        $this->progressCount++;
        if ($result['timeout'] === true) {
            $this->write('<fg=cyan;options=bold>T</fg=cyan;options=bold>', false);
        } elseif (strlen($result['stderr']) > 0) {
            $this->write('<fg=yellow;options=bold>E</fg=yellow;options=bold>', false);
        } elseif ($result['passed'] === true) {
            $this->write('<fg=red;options=bold>M</fg=red;options=bold>', false);
        } else {
            $this->write('<options=bold>.</options=bold>', false);
        }
        if (($this->progressCount % $eolInterval) == 0) {
            $counter = str_pad($this->progressCount, 5, ' ', STR_PAD_LEFT);
            $this->write(' |' . $counter . PHP_EOL, false);
        }
    }

    /**
     * Render a shadow marker, i.e. a mutation whose source code line is
     * not covered by any test based on current code coverage data.
     *
     * @param array $result
     */
    public function renderShadowMark($eolInterval = 60)
    {
        $this->progressCount++;
        $this->write('<fg=blue;options=bold>S</fg=blue;options=bold>', false);
        if (($this->progressCount % $eolInterval) == 0) {
            $counter = str_pad($this->progressCount, 5, ' ', STR_PAD_LEFT);
            $this->write(' |' . $counter . PHP_EOL, false);
        }
    }

    /**
     * Render performance data for the mutation testing run
     *
     */
    public function renderPerformanceData($time, $memory)
    {
        $this->write(
            'Time: <fg=cyan>' . $time . '</fg=cyan> '
            . 'Memory: <fg=cyan>' . $memory . '</fg=cyan>'
        );
    }

    /**
     * Render message that mutation testing loop is starting
     *
     *
     */
    public function renderSummaryReport($total, $kills, $escapes, $errors, $timeouts, $shadows)
    {
        $pkills = str_pad($kills, 8, ' ', STR_PAD_LEFT);
        $pescapes = str_pad($escapes, 8, ' ', STR_PAD_LEFT);
        $perrors = str_pad($errors, 8, ' ', STR_PAD_LEFT);
        $ptimeouts = str_pad($timeouts, 8, ' ', STR_PAD_LEFT);
        $pshadows = str_pad($shadows, 8, ' ', STR_PAD_LEFT);
        $this->write(PHP_EOL, false);
        $this->write($total . ' mutations were generated:');
        $this->write($pkills . ' mutants were killed');
        $this->write($pshadows . ' mutants were not covered by tests');
        $this->write($pescapes . ' covered mutants were not detected');
        $this->write($perrors . ' fatal errors were encountered');
        $this->write($ptimeouts . ' time outs were encountered');
        $this->write(PHP_EOL, false);
        $vanquishedTotal = $kills + $timeouts + $errors;
        $measurableTotal = $total - $shadows;
        if ($measurableTotal !== 0) {
            $detectionRateTested  = round(100 * ($vanquishedTotal / $measurableTotal));
        } else {
            $detectionRateTested  = 0;
        }
        if ($total !== 0) {
            $uncoveredRate = round(100 * ($shadows / $total));
            $detectionRateAll = round(100 * ($vanquishedTotal / $total));
        } else {
            $uncoveredRate = 0;
            $detectionRateAll = 0;
        }
        $this->write('Out of ' . ($total - $shadows) . ' test covered mutations, <options=bold>' . $detectionRateTested . '%</options=bold> were detected.');
        $this->write('Out of ' . $total . ' total mutations, <options=bold>' . $detectionRateAll . '%</options=bold> were detected.');
        $this->write('Out of ' . $total . ' total mutations, <options=bold>' . $uncoveredRate . '%</options=bold> were not covered by tests.');
        $this->write(PHP_EOL, false);
        $this->write('Remember that some mutants will inevitably be harmless (i.e. false positives).');
    }

    /**
     * Render JSON logging message
     *
     *
     */
    public function renderLogToJson($log)
    {
        $this->write('Humbug results are being logged as JSON to: <options=bold>' . $log . '</options=bold>');
    }

    /**
     * Render JSON logging message
     *
     *
     */
    public function renderLogToText($log)
    {
        $this->write('Humbug results are being logged as TEXT to: <options=bold>' . $log . '</options=bold>');
    }

    /**
     * Utility function to prefix output lines with an indent
     *
     * @param string $output
     * @return string
     */
    protected function indent($output)
    {
        $lines = explode("\n", $output);
        $out = [];
        foreach ($lines as $line) {
            $out[] = '    > ' . $line;
        }
        $return = implode("\n", $out);
        return $return;
    }

}
