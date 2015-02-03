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

use Humbug\Config;
use Humbug\Config\JsonParser;
use Humbug\Container;
use Humbug\Mutant;
use Humbug\Utility\Performance;
use Humbug\Utility\ParallelGroup;
use Humbug\Renderer\Text;
use Humbug\Exception\InvalidArgumentException;
use Humbug\Exception\NoCoveringTestsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Finder\Finder;

class Humbug extends Command
{
    protected $container;

    protected $finder;

    private $jsonLogFile;

    private $textLogFile;

    /**
     * Execute the command.
     * The text output, other than some newline management, is held within
     * Humbug\Renderer\Text.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Performance::upMemProfiler();
        $output->writeln($this->getApplication()->getLongVersion() . PHP_EOL);

        $this->validate($input);
        $container = $this->container = new Container($input->getOptions());

        $this->doConfiguration();

        if ($this->isLoggingEnabled()) {
            $this->removeOldLogFiles();
        } else {
            $output->writeln('<error>No log file is specified. Detailed results will not be available.</error>');
        }

        $formatterHelper = new FormatterHelper;
        if ($this->textLogFile) {
            $renderer = new Text($output, $formatterHelper, true);
        } else {
            $renderer = new Text($output, $formatterHelper);
        }

        $renderer->renderPreTestIntroduction();
        $output->write(PHP_EOL);

        /**
         * Log buffered renderer output to file if enabled
         */
        $this->logText($renderer);

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
            if (($count = $container->getAdapter()->hasOks($process->getOutput()))) {
                $progress->setProgress($count);
                $process->clearOutput();
            } elseif (!$container->getAdapter()->ok($process->getOutput())) {
                sleep(1);
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
            'stderr'    => '',
            'coverage'  => 0
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
            $this->logText($renderer);
            return 1;
        }

        /**
         * Capture headline line coverage %.
         * Get code coverage data so we can determine which test suites or
         * or specifications need to be run for each mutation.
         */
        if ($container->getAdapter()->getName() == 'phpunit') {
            $coverage = $container->getAdapter()->getCoverageData($container);
            $result['coverage'] = $coverage->getLineCoverageFrom($container->getCacheDirectory() . '/coverage.humbug.txt');
        } else {
            $specmap = $container->getAdapter()->getSpecMap($container);
        }

        /**
         * Initial test run was a success!
         */
        $renderer->renderInitialRunPass($result, $progress->getProgress(), ($container->getAdapter()->getName() == 'phpunit'));
        $output->write(PHP_EOL);
        $this->logText($renderer);

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
        $this->logText($renderer);

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
        $mutantEscapes = [];
        $mutantErrors = [];
        $mutantTimeouts = [];

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
                if ($container->getAdapter()->getName() == 'phpunit') {
                    $coverage->loadCoverageFor($mutable->getFilename());
                }
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
                        $mutants[$tracker] = new Mutant($mutation, $container);

                        if ($container->getAdapter()->getName() == 'phpunit') {
                            $mutants[$tracker]->setCoverage($coverage);
                        } else {
                            $mutants[$tracker]->setSpecMap($specmap);
                        }

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
                        'passed'     => true,
                        'successful' => $process->isSuccessful(),
                        'timeout'    => false,
                        'stderr'     => $process->getErrorOutput(),
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
                    $this->logText($renderer);

                    if ($result['timeout'] === true) {
                        $countMutantTimeouts++;
                        $mutantTimeouts[] = $mutant;
                    } elseif ($result['successful'] === false) {
                        $countMutantErrors++;
                        $mutantErrors[] = $mutant;
                    } elseif ($result['passed'] === false) {
                        $countMutantKills++;
                    } else {
                        $countMutantEscapes++;
                        $mutantEscapes[] = $mutant;
                    }
                }
            }

            $mutable->cleanup();
        }

        if($container->getAdapter()->getName() == 'phpunit') $coverage->cleanup();
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
        if ($this->jsonLogFile) {
            $renderer->renderLogToJson($this->jsonLogFile);
            $this->logJson(
                $countMutants,
                $countMutantKills,
                $countMutantEscapes,
                $countMutantErrors,
                $countMutantTimeouts,
                $countMutantShadows,
                $mutantEscapes
            );
        }
        if ($this->textLogFile) {
            $renderer->renderLogToText($this->textLogFile);
            $this->logText($renderer);
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
            $this->logText($renderer, implode(PHP_EOL, $out));
        }
        if ($this->jsonLogFile || $this->textLogFile) {
            $output->write(PHP_EOL);
        }

        /**
         * Render performance data
         */
        $renderer->renderPerformanceData(Performance::getTimeString(), Performance::getMemoryUsageString());
        $this->logText($renderer);

        Performance::downMemProfiler();
    }

    protected function logJson($total, $kills, $escapes, $errors, $timeouts, $shadows, array &$mutantEscapes)
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

    protected function doConfiguration()
    {
        $this->container->setBaseDirectory(getcwd());

        $config = (new JsonParser())->parseFile('humbug.json');

        $newConfig = new Config($config);

        $source = $newConfig->getSource();

        $this->finder = $this->prepareFinder(
            isset($source->directories)? $source->directories : null,
            isset($source->excludes)? $source->excludes : null
        );

        $this->container->setSourceList($source);

        $timeout = $newConfig->getTimeout();

        if ($timeout !== null) {
            $this->container->setTimeout((int) $timeout);
        }

        $chDir = $newConfig->getChDir();

        if ($chDir !== null) {
            $this->container->setTestRunDirectory($chDir);
        }

        $this->jsonLogFile = $newConfig->getLogsJson();
        $this->textLogFile = $newConfig->getLogsText();
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

    private function validate(InputInterface $input)
    {
        /**
         * Adapter
         */
        if ($input->getOption('adapter') !== 'phpunit'
        && $input->getOption('adapter') !== 'phpspec') {
            throw new InvalidArgumentException(
                'Only the phpunit or phpspec adapters are supported at this time. Sorry!'
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

    private function logText(Text $renderer, $output = null)
    {
        if ($this->textLogFile) {
            $logText = !is_null($output) ? $output : $renderer->getBuffer();

            file_put_contents(
                $this->textLogFile,
                $logText,
                FILE_APPEND
            );
        }
    }

    private function removeOldLogFiles()
    {
        if (file_exists($this->jsonLogFile)) {
            unlink($this->jsonLogFile);
        }

        if (file_exists($this->textLogFile)) {
            unlink($this->textLogFile);
        }
    }

    private function isLoggingEnabled()
    {
        return $this->jsonLogFile !== null || $this->textLogFile !== null;
    }
}
