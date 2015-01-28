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
use Symfony\Component\Console\Helper\FormatterHelper;

class Text
{

    protected $output;

    protected $progressCount = 0;

    protected $type = OutputInterface::OUTPUT_NORMAL;

    protected $buffer;

    protected $formatterHelper;

    protected $useBuffer = false;

    public function __construct(OutputInterface $output, FormatterHelper $formatterHelper, $buffer = false, $colors = true)
    {
        $this->output = $output;
        if (!$colors) {
            $this->type = OutputInterface::OUTPUT_PLAIN;
        }
        $this->buffer = $buffer;
        $this->formatterHelper = $formatterHelper;
    }

    public function write($string, $eol = true)
    {
        if ($eol) {
            $this->output->writeln($string, $this->type);
        } else {
            $this->output->write($string, false, $this->type);
        }
        if ($this->buffer) {
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
     */
    public function renderPreTestIntroduction()
    {
        $this->write('Humbug running test suite to generate logs and code coverage data...');
    }

    /**
     * Render message where the initial test run didn't pass (excl. incomplete/skipped/risky tests)
     *
     * @param array $result
     * @param int $exitCode
     * @param bool $hasFailure
     */
    public function renderInitialRunFail(array &$result, $exitCode, $hasFailure)
    {
        $error = [];
        $error[] = 'Tests must be in a fully passing state before Humbug is run.';
        $error[] = 'Incomplete, skipped or risky tests are allowed.';
        if ($exitCode !== 0) {
            $error[] = 'The testing framework reported an exit code of ' . $exitCode . '.';
        }
        if ($hasFailure) {
            $error[] = 'The testing framework ran into a failure or error. Refer to output below.';
        }
        if (!empty($result['stdout'])) {
            $error[] = 'Stdout:';
            $error = array_merge($error, $this->indent($result['stdout'], true));
        }
        if (!empty($result['stderr'])) {
            $error[] = 'Stderr:';
            $error = array_merge($error, $this->indent($result['stderr'], true));
        }
        $formattedBlock = $this->formatterHelper->formatBlock($error, 'error');
        $this->write($formattedBlock);
    }

    /**
     * Render message where the initial test run didn't pass (excl. incomplete/skipped/risky tests)
     *
     * @param array $result
     */
    public function renderInitialRunPass()
    {
        $this->write('Humbug has completed the initial test run successfully.');
    }

    /**
     * Render message that Humbug is analysing files.
     */
    public function renderStaticAnalysisStart()
    {
        $this->write('Humbug is analysing source files...');
    }

    /**
     * Render message that mutation testing loop is starting
     *
     * @param int $count
     */
    public function renderMutationTestingStart($count)
    {
        $this->write('Mutation Testing is commencing on ' . $count . ' files...');
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
     * @param int $count
     * @param int $current
     * @param int $eolInterval
     */
    public function renderProgressMark(array &$result, $count, $current, $eolInterval = 60)
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
            $this->write(
                ' |' . $counter . ' ('
                . str_pad($current, strlen($count), ' ', STR_PAD_LEFT)
                . '/' . $count . ')' . PHP_EOL, false);
        }
    }

    /**
     * Render a shadow marker, i.e. a mutation whose source code line is
     * not covered by any test based on current code coverage data.
     *
     * @param int $count
     * @param int $current
     * @param int $eolInterval
     */
    public function renderShadowMark($count, $current, $eolInterval = 60)
    {
        $this->progressCount++;
        $this->write('<fg=blue;options=bold>S</fg=blue;options=bold>', false);
        if (($this->progressCount % $eolInterval) == 0) {
            $counter = str_pad($this->progressCount, 5, ' ', STR_PAD_LEFT);
            $this->write(
                ' |' . $counter . ' ('
                . str_pad($current, strlen($count), ' ', STR_PAD_LEFT)
                . '/' . $count . ')' . PHP_EOL, false);
        }
    }

    /**
     * Render performance data for the mutation testing run
     *
     * @param string $time
     * @param string $memory
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
     * @param int $total
     * @param int $kills
     * @param int $escapes
     * @param int $errors
     * @param int $timeouts
     * @param int $shadows
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
            $coveredRate = round(100 * (($total - $shadows) / $total));
            $detectionRateAll = round(100 * ($vanquishedTotal / $total));
        } else {
            $coveredRate = 0;
            $detectionRateAll = 0;
        }
        $this->write('Out of ' . ($total - $shadows) . ' test covered mutations, <options=bold>' . $detectionRateTested . '%</options=bold> were detected.');
        $this->write('Out of ' . $total . ' total mutations, <options=bold>' . $detectionRateAll . '%</options=bold> were detected.');
        $this->write('Out of ' . $total . ' total mutations, <options=bold>' . $coveredRate . '%</options=bold> were covered by tests.');
        $this->write(PHP_EOL, false);
        $this->write('Remember that some mutants will inevitably be harmless (i.e. false positives).');
    }

    /**
     * Render JSON logging message
     *
     * @param string $log
     */
    public function renderLogToJson($log)
    {
        $this->write('Humbug results are being logged as JSON to: <options=bold>' . $log . '</options=bold>');
    }

    /**
     * Render JSON logging message
     *
     * @param string $log
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
    protected function indent($output, $asArray = false)
    {
        $lines = explode("\n", $output);
        $out = [];
        foreach ($lines as $line) {
            $out[] = '   > ' . $line;
        }
        if ($asArray) {
            return $out;
        }
        $return = implode("\n", $out);
        return $return;
    }
}
