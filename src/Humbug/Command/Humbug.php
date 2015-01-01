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
use Humbug\Utility\TestTimeAnalyser;
use Humbug\Adapter\Phpunit;
use Humbug\Utility\Performance;
use Humbug\Utility\ParallelGroup;
use Humbug\Utility\CoverageData;
use Humbug\Renderer\Text;
use Humbug\Exception\InvalidArgumentException;
use Humbug\Exception\NoCoveringTestsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Humbug extends Command
{

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
        $output->writeln($this->getApplication()->getLongVersion() . PHP_EOL);

        $this->validate($input, $output);
        $container = new Container($input, $output);
        
        if ($input->hasOption('log-text')) {
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
        $adapter = $container->getAdapter();
        $process = $adapter->runTests($container, true, true);
        $process->run();
        $result = [ // default values
            'passed'    => true,
            'timeout'   => false,
            'stdout'    => '',
            'stderr'    => ''
        ];
        $result['stdout'] = $process->getOutput();
        $result['stderr'] = $process->getErrorOutput();
        if (!$adapter->processOutput($result['stdout'])) {
            $result['passed'] = false;
        }

        /**
         * Check if the initial test run ended with a fatal error
         */
        if (!empty($result['stderr'])) {
            $renderer->renderInitialRunError($result);
            $this->logText($input, $renderer);
            exit(1);
        }

        /**
         * Check if the initial test run was not in a passing state
         */
        if ($result['passed'] === false) {
            $renderer->renderInitialRunFail($result);
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
        $orderedTestCases = null;
        $analyser = new TestTimeAnalyser(
            $container->getCacheDirectory() . '/junitlog.humbug.xml'
        );
        $orderedTestCases = $analyser->process()->getTestCases();

        /**
         * Message re Static Analysis
         */
        $renderer->renderStaticAnalysisStart();
        $output->write(PHP_EOL);

        /**
         * Examine all source code files and collect up mutations to apply
         */
        $mutables = $container->getMutables();

        /**
         * Message re Mutation Testing starting
         */
        $renderer->renderMutationTestingStart();
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
         */
        $parallels = 1;

        $coverage = new CoverageData(
            $container->getCacheDirectory() . '/coverage.humbug.php',
            $analyser
        );

        foreach ($mutables as $i => $mutable) {
            $mutations = $mutable->generate()->getMutations();
            $batches = array_chunk($mutations, $parallels);

            foreach ($batches as $batch) {
                $processes = [];
                // Being utterly paranoid, track index using $tracker explicitly
                // to ensure process->mutation indices are linked for reporting.
                foreach ($batch as $tracker => $mutation) {

                    try {
                        $orderedTestCases = $coverage->getOrderedTestCases(
                            $mutation['file'],
                            $mutation['line']
                        );

                        $processes[$tracker] = $container->getAdapter()->runTests(
                            $container,
                            true,
                            false,
                            $mutation,
                            $orderedTestCases
                        );
                    } catch (NoCoveringTestsException $e) {
                        /**
                         * No tests excercise the mutated line. We'll report
                         * the uncovered mutants separately and omit them
                         * from final score.
                         */
                        $countMutants++;
                        $countMutantShadows++;
                        $renderer->renderShadowMark();
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

                foreach ($processes as $tracker => $process) {

                    /**
                     * Define the result for each process
                     */
                    $result = [
                        'passed'    => true,
                        'timeout'   => false,
                        'stdout'    => '',
                        'stderr'    => ''
                    ];

                    $result['stdout'] = $process->getOutput();
                    $result['stderr'] = $process->getErrorOutput();
                    if ($group->timedOut($tracker)) {
                        $result['timeout'] = true;
                    }
                    if (!$adapter->processOutput($result['stdout'])) {
                        $result['passed'] = false;
                    }

                    /**
                     * Handle the defined result for each process
                     */
                    $countMutants++;

                    $renderer->renderProgressMark($result);
                    $this->logText($input, $renderer);

                    if ($result['timeout'] === true) {
                        $countMutantTimeouts++;
                    } elseif (!empty($result['stderr'])) {
                        $countMutantErrors++;
                    } elseif ($result['passed'] === false) {
                        $countMutantKills++;
                    } else {
                        $countMutantEscapes++;
                        $batch[$tracker]['mutation']->mutate(
                            $batch[$tracker]['tokens'],
                            $batch[$tracker]['index']
                        );
                        $mutantEscapes[] = [
                            'mutation'  => $batch[$tracker],
                            'stdout'    => $result['stdout']
                        ];
                    }
                }
            }

            $mutable->cleanup();
            unset($this->_mutables[$i]); //or null
        }

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
         * Render performance data
         */
        $renderer->renderPerformanceData(Performance::getTimeString(), Performance::getMemoryUsageString());
        $this->logText($input, $renderer);

        /**
         * Render detailed report with information on escapee diffs
         */
        /*$renderer->renderDetailedReport(
            $mutantEscapes
        );
        $this->logText($input, $renderer);*/

        /**
         * Render any closing messages?
         */
    }

    protected function configure()
    {
        $dirs = $this->checkDirectories();
        $this
            ->setName('humbug')
            ->setDescription('Run Humbug for target tests')
            ->addOption(
               'basedir',
               'B',
               InputOption::VALUE_REQUIRED,
               'Set base directory from where to run tests.',
                $dirs['base']
            )
            ->addOption(
               'srcdir',
               'S',
               InputOption::VALUE_REQUIRED,
               'Set source directory for the files to be tested.',
                $dirs['source']
            )
            ->addOption(
               'testdir',
               'T',
               InputOption::VALUE_REQUIRED,
               'Set tests directory if required to change directories to run tests.'
            )
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
                60
            )
            ->addOption(
               'detail',
               'd',
               InputOption::VALUE_REQUIRED,
               'Add more details, including test results, about mutations which induced test failures.',
                0
            )
            ->addOption(
               'log-text',
               null,
               InputOption::VALUE_REQUIRED,
               'Log output to the given text file.',
                0
            )
        ;
    }

    protected function checkDirectories()
    {
        $dirs = [];
        $dirs['base'] = getcwd();
        if (file_exists($dirs['base'] . '/src')) {
            $dirs['source'] = $dirs['base'] . '/src';
        } elseif (file_exists($dirs['base'] . '/lib')) {
            $dirs['source'] = $dirs['base'] . '/lib';
        } elseif (file_exists($dirs['base'] . '/library')) {
            $dirs['source'] = $dirs['base'] . '/library';
        }
        if (!isset($dirs['source'])) {
            $dirs['source'] = 'UNABLE TO DETECT; SPECIFY';
        }
        return $dirs;
    }

    protected function validate(InputInterface $input, OutputInterface $output)
    {
        /**
         * Base directory
         */
        if (!file_exists($input->getOption('basedir'))) {
            throw new InvalidArgumentException(
                'The base directory specified does not exist or could not be read.'
            );
        }
        /**
         * Source directory
         */
        if (!file_exists($input->getOption('srcdir'))) {
            throw new InvalidArgumentException(
                'The source directory specified does not exist or could not be '
                . 'automatically detected. Please specify in the options'
            );
        }
        /**
         * Tests directory
         */
        if (!empty($input->getOption('testdir')) && !file_exists($input->getOption('testdir'))) {
            throw new InvalidArgumentException(
                'The tests directory specified does not exist or could not be '
                . 'automatically detected. Please specify in the options'
            );
        }
        /**
         * Adapter
         */
        if ($input->getOption('adapter') !== 'phpunit') {
            throw new InvalidArgumentException(
                'Only a PHPUnit adapter is supported at this time. Sorry!'
            );
        }
        /**
         * Adapter Options
         */
        /**
         * Adapter Constraints
         */
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
        /**
         * Detailed Captures
         */
        if (!is_numeric($input->getOption('detail')) || $input->getOption('detail') < 0
        || $input->getOption('detail') > 1) {
            throw new InvalidArgumentException(
                'The detail flag must be either 0 or 1'
            );
        }
        /**
         * Logging to text file
         */
        if ($input->hasOption('log-text')) {
            if (file_exists($input->getOption('log-text'))) {
                unlink($input->getOption('log-text'));
            }
        }
    }

    protected function logText(InputInterface $input, $renderer)
    {
        if ($input->hasOption('log-text')) {
            file_put_contents(
                $input->getOption('log-text'),
                $renderer->getBuffer(),
                FILE_APPEND
            );
        }
    }
}