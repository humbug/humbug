<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Command;

use Humbug\Container;
use Humbug\Adapter\Phpunit;
use Humbug\Mutant;
use Humbug\Utility\Performance;
use Humbug\Utility\ParallelGroup;
use Humbug\Renderer\Text;
use Humbug\Exception\InvalidArgumentException;
use Humbug\Exception\NoCoveringTestsException;
use Humbug\Exception\JsonConfigException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Finder\Finder;

class Humbug extends Command
{

    protected $finder;

    protected $logJson = false;

    protected $jsonLogFile;

    protected $logText = false;

    protected $textLogFile;

    /**
     * Execute the command.
     * The text output, other than some newline management, is held within
     * Humbug\Renderer\Text.
     *
     * @param   Symfony\Component\Console\Input\InputInterface
     * @param   Symfony\Component\Console\Output\OutputInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Performance::upMemProfiler();
        $output->writeln($this->getApplication()->getLongVersion() . PHP_EOL);

        $this->validate($input, $output);
        $container = $this->container = new Container($input, $output);

        /**
         * Setup source code finder and timeout if set
         */
        $this->doConfiguration($output);


        if ($this->logText === true) {
            $renderer = new Text($output, true);
        } else {
            $renderer = new Text($output);
        }

        $renderer->renderPreTestIntroduction();
        $output->write(PHP_EOL);

        /**
         * Log buffered renderer output to file if enabled
         */
        $this->logText($input, $renderer);

        /**
         * Make initial test run to ensure tests are in a starting passing state
         * and also log the results so test runs during the mutation phase can
         * be optimised.
         */
        $process = $container->getAdapter()->getProcess($container, true);
        $progress = new ProgressBar($output);
        $progress->setFormat('verbose');
        $progress->setBarWidth(58);
        $progress->start();

        $process->start();
        usleep(1000);
        $hasFailure = false;
        while ($process->isRunning()) {
            usleep(2500);
            if (preg_match("%[\n\r]+ok (\\d+).*$%", $process->getOutput(), $matches)) {
                $progress->setProgress((int) $matches[1]);
                $process->clearOutput();
            } elseif (preg_match("%[\n\r]+not ok (\\d+).*$%", $process->getOutput(), $matches)) {
                $hasFailure = true;
                break;
            }
        }
        $process->stop();
        $progress->finish();
        $output->write(PHP_EOL.PHP_EOL);
        $exitCode = $process->getExitCode();

        $result = [ // default values
            'passed'    => true,
            'timeout'   => false,
            'stdout'    => '',
            'stderr'    => ''
        ];
        $result['stdout'] = $process->getOutput();
        $result['stderr'] = $process->getErrorOutput();
        if (!$container->getAdapter()->ok($result['stdout'])) {
            $result['passed'] = false;
        }

        /**
         * Check if the initial test run ended with a fatal error
         */
        if ($exitCode !== 0 || $hasFailure) {
            $renderer->renderInitialRunFail($result, $exitCode, $hasFailure);
            $this->logText($input, $renderer);
            exit(1);
        }

        /**
         * Initial test run was a success!
         */
        $renderer->renderInitialRunPass($result);
        $output->write(PHP_EOL);
        $this->logText($input, $renderer);

        /**
         * Analyse initial run logs to optimise future test runs by ordering
         * the test classes to uses fastest-first.
         *
         * TODO: Move to adapter eventually
         */
        $analyser = $container->getAdapter()->getLogAnalyser($container);
        $analyser->process();
        $coverage = $container->getAdapter()->getCoverageData($container, $analyser);

        /**
         * Message re Static Analysis
         */
        $renderer->renderStaticAnalysisStart();
        $output->write(PHP_EOL);

        /**
         * Examine all source code files and collect up mutations to apply
         */
        $mutables = $container->getMutableFiles($this->finder);

        /**
         * Message re Mutation Testing starting
         */
        $renderer->renderMutationTestingStart(count($mutables));
        $output->write(PHP_EOL);
        Performance::start();
        $this->logText($input, $renderer);

        /**
         * Iterate across all mutations. After each, run the test suite and
         * collect data on how tests handled the mutations. We use ext/runkit
         * to dynamically alter included (in-memory) classes on the fly.
         */
        $countMutants = 0;
        $countMutantKills = 0;
        $countMutantEscapes = 0;
        $countMutantErrors = 0;
        $countMutantTimeouts = 0;
        $countMutantShadows = 0;
        $mutantKills = [];
        $mutantEscapes = [];
        $mutantErrors = [];
        $mutantTimeouts = [];
        $mutantShadows = [];

        /**
         * We can do parallel runs, but typically two test runs will compete for
         * any uninsulated resources (e.g. files/database) so hardcoded to 1 for now.
         *
         * TODO: Move PHPUnit specific stuff to adapter...
         */
        $parallels = 1;

        /**
         * MUTATION TESTING!
         */
        foreach ($mutables as $i => $mutable) {
            $mutations = $mutable->generate()->getMutations();
            $batches = array_chunk($mutations, $parallels);
            unset($mutations);

            try {
                $coverage->loadCoverageFor($mutable->getFilename());
            } catch (NoCoveringTestsException $e) {
                foreach ($batches as $batch) {
                    $countMutants++;
                    $countMutantShadows++;
                    $renderer->renderShadowMark(count($mutables), $i);
                }
                continue;
            }

            foreach ($batches as $batch) {
                $mutants = [];
                $processes = [];
                // Being utterly paranoid, track index using $tracker explicitly
                // to ensure process->mutation indices are linked for reporting.
                foreach ($batch as $tracker => $mutation) {
                    try {
                        /**
                         * Unleash the Mutant!
                         */
                        $mutants[$tracker] = new Mutant($mutation, $container, $coverage);

                        $processes[$tracker] = $mutants[$tracker]->getProcess();
                    } catch (NoCoveringTestsException $e) {
                        /**
                         * No tests excercise the mutated line. We'll report
                         * the uncovered mutants separately and omit them
                         * from final score.
                         */
                        $countMutants++;
                        $countMutantShadows++;
                        $renderer->renderShadowMark(count($mutables), $i);
                    }
                }

                /**
                 * Check if the whole batch has been eliminated as uncovered
                 * by any tests
                 */
                if (count($processes) == 0) {
                    continue;
                }

                $group = new ParallelGroup($processes);
                $group->run();

                foreach ($mutants as $tracker => $mutant) {
                    $process = $mutant->getProcess();

                    /**
                     * Define the result for each process
                     */
                    $result = [
                        'passed'    => true,
                        'timeout'   => false,
                        'stderr'    => $process->getErrorOutput(),
                    ];

                    if ($group->timedOut($tracker)) {
                        $result['timeout'] = true;
                    }
                    if (!$container->getAdapter()->ok($process->getOutput())) {
                        $result['passed'] = false;
                    }
                    $process->clearOutput();

                    /**
                     * Handle the defined result for each process
                     */
                    $countMutants++;

                    $renderer->renderProgressMark($result, count($mutables), $i);
                    $this->logText($input, $renderer);

                    if ($result['timeout'] === true) {
                        $countMutantTimeouts++;
                        $mutantTimeouts[] = $mutant;
                    } elseif (!$process->isSuccessful()) {
                        $countMutantErrors++;
                        $mutantErrors[] = $mutant;
                    } elseif ($result['passed'] === false) {
                        $countMutantKills++;
                        //$mutantKills[] = $mutant;
                    } else {
                        $countMutantEscapes++;
                        $mutantEscapes[] = $mutant;
                    }
                }
            }

            $mutable->cleanup();
        }

        $coverage->cleanup();
        Performance::stop();

        /**
         * Render summary report with stats
         */
        $output->write(PHP_EOL);
        $renderer->renderSummaryReport(
            $countMutants,
            $countMutantKills,
            $countMutantEscapes,
            $countMutantErrors,
            $countMutantTimeouts,
            $countMutantShadows
        );
        $output->write(PHP_EOL);

        /**
         * Do any detailed logging now
         */
        if ($this->logJson === true) {
            $renderer->renderLogToJson($this->jsonLogFile);
            $this->logJson(
                $countMutants,
                $countMutantKills,
                $countMutantEscapes,
                $countMutantErrors,
                $countMutantTimeouts,
                $countMutantErrors,
                $mutantEscapes,
                $mutantErrors,
                $this->jsonLogFile
            );
        }
        if ($this->logText === true) {
            $renderer->renderLogToText($this->textLogFile);
            $this->logText($input, $renderer);
            $out = [PHP_EOL, '-------', 'Escapes', '-------'];
            foreach ($mutantEscapes as $index => $escaped) {
                $mutation = $escaped->getMutation();
                $out[] = $index+1 . ') ' . $mutation['mutator'];
                $out[] = 'Diff on ' . $mutation['class'] . '::' . $mutation['method'] . '() in ' . $mutation['file'] . ':';
                $out[] = $escaped->getDiff();
                $out[] = PHP_EOL;
            }

            if (count($mutantTimeouts) > 0) {
                $out = array_merge($out, [PHP_EOL, '------', 'Timeouts', '------']);
                foreach ($mutantTimeouts as $index => $timeouted) {
                    $mutation = $timeouted->getMutation();
                    $out[] = $index+1 . ') ' . $mutation['mutator'];
                    $out[] = 'Diff on ' . $mutation['class'] . '::' . $mutation['method'] . '() in ' . $mutation['file'] . ':';
                    $out[] = $timeouted->getDiff();
                    $out[] = PHP_EOL;
                }
            }

            if (count($mutantErrors) > 0) {
                $out = array_merge($out, [PHP_EOL, '------', 'Errors', '------']);
                foreach ($mutantErrors as $index => $errored) {
                    $mutation = $errored->getMutation();
                    $out[] = $index+1 . ') ' . $mutation['mutator'];
                    $out[] = 'Diff on ' . $mutation['class'] . '::' . $mutation['method'] . '() in ' . $mutation['file'] . ':';
                    $out[] = $errored->getDiff();
                    $out[] = PHP_EOL;
                    $out[] = 'The following output was received on stderr:';
                    $out[] = PHP_EOL;
                    $out[] = $errored->getProcess()->getErrorOutput();
                    $out[] = PHP_EOL;
                    $out[] = PHP_EOL;
                }
            }
            $this->logText($input, $renderer, implode(PHP_EOL, $out));
        }
        if ($this->logJson === true || $this->logText === true) {
            $output->write(PHP_EOL);
        }

        /**
         * Render performance data
         */
        $renderer->renderPerformanceData(Performance::getTimeString(), Performance::getMemoryUsageString());
        $this->logText($input, $renderer);

        Performance::downMemProfiler();
    }

    protected function logJson($total, $kills, $escapes, $errors, $timeouts, $shadows, array &$mutantEscapes, array $mutantShadows, $file)
    {
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
        $out = [
            'summary' => [
                'total' => $total,
                'kills' => $kills,
                'escapes' => $escapes,
                'errors' => $errors,
                'timeouts' => $timeouts,
                'notests' => $shadows,
                'covered_score' => $detectionRateTested,
                'combined_score' => $detectionRateAll,
                'mutation_coverage' => (100 - $uncoveredRate)
            ],
            'escaped' => []
        ];
        foreach ($mutantEscapes as $escaped) {
            $out['escaped'][] = $escaped->toArray();
        }
        file_put_contents(
            $this->jsonLogFile,
            json_encode($out, JSON_PRETTY_PRINT)
        );
    }

    protected function prepareFinder($directories, $excludes)
    {
        $finder = new Finder;
        $finder->files()->name('*.php');

        if ($directories) {
            foreach ($directories as $directory) {
                $finder->in($directory);
            }
        } else {
            $finder->in('.');
        }

        if (isset($excludes)) {
            foreach ($excludes as $exclude) {
                $finder->exclude($exclude);
            }
        }

        return $finder;
    }

    protected function doConfiguration(OutputInterface $output)
    {
        if (!file_exists('humbug.json')) {
            throw new JsonConfigException(
                'Configuration file does not exist. Please create a humbug.json file.'
            );
        }
        $config = json_decode(file_get_contents('humbug.json'));
        if (null === $config || json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonConfigException(
                'Error parsing configuration file JSON'
                . (function_exists('json_last_error_msg') ? ': ' . json_last_error_msg() : '')
            );
        }
        $this->container->setBaseDirectory(getcwd());

        /**
         * Check for source code scanning config
         */
        if (!isset($config->source)) {
            throw new JsonConfigException(
                'Source code data is not included in configuration file'
            );
        }
        if (!isset($config->source->directories) && !isset($config->source->excludes)) {
            throw new JsonConfigException(
                'You must set at least one source directory or exclude in the configuration file'
            );
        }
        $this->finder = $this->prepareFinder(
            isset($config->source->directories)? $config->source->directories : null,
            isset($config->source->excludes)? $config->source->excludes : null
        );
        $this->container->setSourceList($config->source);

        /**
         * Check for timeout config
         */
        if (isset($config->timeout)) {
            $this->container->setTimeout((int) $config->timeout);
        }

        /**
         * Check for change working directory config
         */
        if (isset($config->chdir)) {
            if (!file_exists($config->chdir)) {
                throw new JsonConfigException(
                    'Directory in which to run tests does not exist: ' . $config->chdir
                );
            }
            $this->container->setTestRunDirectory($config->chdir);
        }

        /**
         * Check for logging config
         */
        if (!isset($config->logs) || (!isset($config->logs->json) && !isset($config->logs->text))) {
            $output->writeln('<error>No log file is specified. Detailed results will not be available.</error>');
        } else {
            if (isset($config->logs->json)) {
                if (!file_exists(dirname($config->logs->json))) {
                    throw new JsonConfigException(
                        'Directory for json logging does not exist: ' . dirname($config->logs->json)
                    );
                }
                $this->logJson = true;
                $this->jsonLogFile = $config->logs->json;
                if (file_exists($this->jsonLogFile)) {
                    unlink($this->jsonLogFile);
                }
            }
            if (isset($config->logs->text)) {
                if (!file_exists(dirname($config->logs->text))) {
                    throw new JsonConfigException(
                        'Directory for text logging does not exist: ' . dirname($config->logs->text)
                    );
                }
                $this->logText = true;
                $this->textLogFile = $config->logs->text;
                if (file_exists($this->textLogFile)) {
                    unlink($this->textLogFile);
                }
            }
        }
    }

    protected function configure()
    {
        $this
            ->setName('humbug')
            ->setDescription('Run Humbug for target tests')
            ->addOption(
               'adapter',
               'a',
               InputOption::VALUE_REQUIRED,
               'Set name of the test adapter to use.',
                'phpunit'
            )
            ->addOption(
               'options',
               'o',
               InputOption::VALUE_REQUIRED,
               'Set command line options string to pass to test adapter. '
                    . 'Default is dictated dynamically by '.'Humbug'.'.'
            )
            ->addOption(
               'constraints',
               'c',
               InputOption::VALUE_REQUIRED,
               'Options set on adapter to constrain which tests are run. '
                    . 'Applies only to the very first initialising test run.'
            )
            ->addOption(
               'timeout',
               't',
               InputOption::VALUE_REQUIRED,
               'Sets a timeout applied for each test run to combat infinite loop mutations.',
                10
            )
        ;
    }

    protected function validate(InputInterface $input, OutputInterface $output)
    {
        /**
         * Adapter
         */
        if ($input->getOption('adapter') !== 'phpunit') {
            throw new InvalidArgumentException(
                'Only a PHPUnit adapter is supported at this time. Sorry!'
            );
        }
        /**
         * Timeout
         */
        if (!is_numeric($input->getOption('timeout')) || $input->getOption('timeout') <= 0) {
            throw new InvalidArgumentException(
                'The timeout must be an integer specifying a number of seconds. '
                . 'A number greater than zero is expected, and greater than maximum '
                . 'test suite execution time under any given constraint option is '
                . 'highly recommended.'
            );
        }
    }

    protected function logText(InputInterface $input, Text $renderer, $output = null)
    {
        if ($this->logText === true) {
            if (!is_null($output)) {
                file_put_contents(
                    $this->textLogFile,
                    $output,
                    FILE_APPEND
                );
            } else {
                file_put_contents(
                    $this->textLogFile,
                    $renderer->getBuffer(),
                    FILE_APPEND
                );
            }
        }
    }
}
