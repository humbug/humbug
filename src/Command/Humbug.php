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

use Humbug\Adapter\AdapterAbstract;
use Humbug\Collector;
use Humbug\Config;
use Humbug\Config\JsonParser;
use Humbug\Container;
use Humbug\Adapter\Phpunit;
use Humbug\Mutant;
use Humbug\ProcessRunner;
use Humbug\Report\Text as TextReport;
use Humbug\Utility\Performance;
use Humbug\Utility\ParallelGroup;
use Humbug\Renderer\Text;
use Humbug\Exception\InvalidArgumentException;
use Humbug\Exception\NoCoveringTestsException;
use Humbug\File\Collector as FileCollector;
use Humbug\File\Collection as FileCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput as EmptyInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\PhpProcess;

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

        $this->validate($input);
        $container = $this->container = new Container($input->getOptions());

        $this->doConfiguration($input);

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
        $progressBar = null;
        if (!$input->getOption('no-progress-bar')) {
            $progressBar = new ProgressBar($output);
            $progressBar->setFormat('verbose');
            $progressBar->setBarWidth(58);
            if (!$output->isDecorated()) {
                $progressBar->setRedrawFrequency(60);
            }
            $progressBar->start();
        }

        $testFrameworkAdapter = $container->getAdapter();

        $process = $testFrameworkAdapter->getProcess($container, true);

        $hasFailure = $this->performInitailTestsRun($process, $testFrameworkAdapter, $progressBar);

        if (!$input->getOption('no-progress-bar')) {
            $progressBar->finish();
            $output->write(PHP_EOL.PHP_EOL);
        }

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
        $coverage = $container->getAdapter()->getCoverageData($container);
        $result['coverage'] = $coverage->getLineCoverageFrom($container->getTempDirectory() . '/coverage.humbug.txt');

        /**
         * Initial test run was a success!
         */
        $renderer->renderInitialRunPass($result, $progressBar);
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
         * Setup caching of files in use. We can improve performance by not repeating
         * mutations where neither the underlying file nor matching tests have
         * changed.
         */
        $fileCollector = new FileCollector(new FileCollection);
        $testCollector = new FileCollector(new FileCollection);
        $cachedFileCollection = $this->getCachedFileCollection('source_files.json');
        $cachedTestCollection = $this->getCachedFileCollection('test_files.json');

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
        $countMutantShadows = 0;
        $collector = new Collector();

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
            $fileCollector->collect($mutable->getFilename());

            $mutations = $mutable->generate()->getMutations();
            $batches = array_chunk($mutations, $parallels);
            unset($mutations);

            try {
                $coverage->loadCoverageFor($mutable->getFilename());
            } catch (NoCoveringTestsException $e) {
                foreach ($batches as $batch) {
                    $collector->collectShadow();
                    $renderer->renderShadowMark(count($mutables), $i);
                }
                continue;
            }

            /**
             * TODO: Cache results and import them here.
             */
            $testFilesHaveChanged = $this->testFilesHaveChanged(
                $testCollector,
                $cachedTestCollection,
                $coverage,
                $container->getAdapter(),
                $mutable->getFilename()
            );
            $sourceFilesHaveChanged =
                $cachedFileCollection->hasFile($mutable->getFilename()) === false
                || (
                $cachedFileCollection->getFileHash($mutable->getFilename())
                !== $fileCollector->getCollection()->getFileHash($mutable->getFilename())
                )
            ;
            if ($input->getOption('incremental') && $testFilesHaveChanged === false
            && $sourceFilesHaveChanged === false) {
                // skip this process and use cached results when ready if IA enabled
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
                        $collector->collectShadow();
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
                        'passed'     => $container->getAdapter()->ok($process->getOutput()),
                        'successful' => $process->isSuccessful(),
                        'timeout'    => $group->timedOut($tracker),
                        'stderr'     => $process->getErrorOutput(),
                    ];

                    $process->clearOutput();

                    /**
                     * Handle the defined result for each process
                     */

                    $renderer->renderProgressMark($result, count($mutables), $i);
                    $this->logText($renderer);

                    $collector->collect($mutant, $result);
                }
            }

            $mutable->cleanup();
        }

        $coverage->cleanup();
        $fileCollector->write($container->getWorkingCacheDirectory() . '/source_files.json');
        $testCollector->write($container->getWorkingCacheDirectory() . '/test_files.json');
        Performance::stop();

        /**
         * Render summary report with stats
         */
        $output->write(PHP_EOL);
        $renderer->renderSummaryReport($collector);
        $output->write(PHP_EOL);

        /**
         * Do any detailed logging now
         */
        if ($this->jsonLogFile) {
            $renderer->renderLogToJson($this->jsonLogFile);
            $this->logJson($collector);
        }
        if ($this->textLogFile) {
            $renderer->renderLogToText($this->textLogFile);
            $this->logText($renderer);

            $textReport = $this->prepareTextReport($collector);
            $this->logText($renderer, $textReport);
        }
        if ($this->jsonLogFile || $this->textLogFile) {
            $output->write(PHP_EOL);
        }

        /**
         * Render performance data
         */
        if (!$input->getOption('no-progress-bar')) {
            $renderer->renderPerformanceData(
                Performance::getTimeString(),
                Performance::getMemoryUsageString()
            );
        }
        $this->logText($renderer);
        Performance::downMemProfiler();
    }

    protected function logJson(Collector $collector)
    {
        $vanquishedTotal = $collector->getVanquishedTotal();
        $measurableTotal = $collector->getMeasurableTotal();
        if ($measurableTotal !== 0) {
            $detectionRateTested  = round(100 * ($vanquishedTotal / $measurableTotal));
        } else {
            $detectionRateTested  = 0;
        }
        if ($collector->getTotalCount() !== 0) {
            $uncoveredRate = round(100 * ($collector->getShadowCount() / $collector->getTotalCount()));
            $detectionRateAll = round(100 * ($collector->getVanquishedTotal() / $collector->getTotalCount()));
        } else {
            $uncoveredRate = 0;
            $detectionRateAll = 0;
        }
        $out = [
            'summary' => [
                'total' => $collector->getTotalCount(),
                'kills' => $collector->getKilledCount(),
                'escapes' => $collector->getEscapeCount(),
                'errors' => $collector->getErrorCount(),
                'timeouts' => $collector->getTimeoutCount(),
                'notests' => $collector->getShadowCount(),
                'covered_score' => $detectionRateTested,
                'combined_score' => $detectionRateAll,
                'mutation_coverage' => (100 - $uncoveredRate)
            ],
            'escaped' => []
        ];
        $out = array_merge($out, $collector->toGroupedMutantArray());
        file_put_contents(
            $this->jsonLogFile,
            json_encode($out, JSON_PRETTY_PRINT)
        );
    }

    protected function prepareFinder($directories, $excludes, array $names = null)
    {
        $finder = new Finder;
        $finder->files();
        if (!is_null($names) && count($names) > 0) {
            foreach ($names as $name) {
                $finder->name($name);
            }
        } else {
            $finder->name('*.php');
        }
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

    protected function doConfiguration(InputInterface $input)
    {
        $this->container->setBaseDirectory(getcwd());
        $config = (new JsonParser())->parseFile();
        $newConfig = new Config($config);
        $source = $newConfig->getSource();
        $this->finder = $this->prepareFinder(
            isset($source->directories)? $source->directories : null,
            isset($source->excludes)? $source->excludes : null,
            $input->getOption('file')
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

    protected function getCachedFileCollection($cache)
    {
        if (file_exists($this->container->getWorkingCacheDirectory() . '/' . $cache)) {
            $cachedFileCollection = new FileCollection(json_decode(
                $this->container->getWorkingCacheDirectory() . '/' . $cache,
                true
            ));
        } else {
            $cachedFileCollection = new FileCollection;
        }
        return $cachedFileCollection;
    }

    protected function configure()
    {
        $this
            ->setName('run')
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
               'file',
               'f',
               InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
               'Pattern representing file(s) to mutate. Can set more than once.'
            )
            ->addOption(
               'constraints',
               'c',
               InputOption::VALUE_REQUIRED,
               'Options set on adapter to constrain which tests are run. '
                    . 'Applies only to the very first test run.'
            )
            ->addOption(
               'timeout',
               't',
               InputOption::VALUE_REQUIRED,
               'Sets a timeout applied for each test run to combat infinite loop mutations.',
                10
            )
            ->addOption(
               'no-progress-bar',
               'b',
               InputOption::VALUE_NONE,
               'Removes dynamic output like the progress bar and performance data from output.'
            )
            ->addOption(
               'incremental',
               'i',
               InputOption::VALUE_NONE,
               'Enable incremental mutation testing by relying on cached results.'
            )
        ;
    }

    private function validate(InputInterface $input)
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

    private function prepareTextReport(Collector $collector)
    {
        $textReport = new TextReport();

        $out = $textReport->prepareMutantsReport($collector->getEscaped(), 'Escapes');

        if ($collector->getTimeoutCount() > 0) {
            $out .= PHP_EOL . $textReport->prepareMutantsReport($collector->getTimeouts(), 'Timeouts');
        }

        if ($collector->getErrorCount() > 0) {
            $out .= PHP_EOL . $textReport->prepareMutantsReport($collector->getErrors(), 'Errors');
        }

        return $out;
    }

    private function performInitailTestsRun(
        PhpProcess $process,
        AdapterAbstract $testFrameworkAdapter,
        ProgressBar $progressBar = null
    ) {
        if (!is_null($progressBar)) {
            $setProgressBarProgressCallback = function ($count) use ($progressBar) {
                $progressBar->setProgress($count);
            };

            return (new ProcessRunner())->run($process, $testFrameworkAdapter, $setProgressBarProgressCallback);
        }
        return (new ProcessRunner())->run($process, $testFrameworkAdapter);
    }

    private function testFilesHaveChanged(
        FileCollector $collector,
        FileCollection $cached,
        \Humbug\Utility\CoverageData $coverage,
        AdapterAbstract $adapter,
        $file)
    {
        $result = false;
        $tests = $coverage->getAllTestClasses($file);
        foreach ($tests as $test) {
            $file = $adapter->getClassFile($test, $this->container);
            $collector->collect($file);
            if (!$cached->hasFile($file)
            || $collector->getCollection()->getFileHash($file) !== $cached->getFileHash($file)) {
                $result = true;
            }
        }
        return $result;
    }
}
