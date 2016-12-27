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
use Humbug\Container;
use Humbug\Config\JsonParser;
use Humbug\Exception\InvalidArgumentException;
use Humbug\File\Collector as FileCollector;
use Humbug\File\Collection as FileCollection;
use Humbug\MutableIterator;
use Humbug\Renderer\Text;
use Humbug\TestSuite\Mutant\Builder as MutantBuilder;
use Humbug\TestSuite\Mutant\IncrementalCache;
use Humbug\TestSuite\Unit\Observers\LoggingObserver;
use Humbug\TestSuite\Unit\Observers\ProgressBarObserver;
use Humbug\TestSuite\Unit\Runner as UnitTestRunner;
use Humbug\Utility\Performance;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Humbug extends Command
{
    protected $container;

    /**
     * @var MutantBuilder
     */
    protected $builder;

    /**
     * @var MutableIterator
     */
    protected $mutableIterator;

    /**
     * @var string
     */
    private $jsonLogFile;

    /**
     * @var string
     */
    private $textLogFile;

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (PHP_SAPI !== 'phpdbg' && !defined('HHVM_VERSION') && !extension_loaded('xdebug')) {
            $output->writeln(
                '<error>You need to install and enable xdebug, or use phpdbg, '
                . 'in order to allow for code coverage generation.</error>'
            );
            return 1;
        }

        Performance::upMemProfiler();

        $this->validate($input);
        $container = $this->container = new Container($input->getOptions());

        $this->doConfiguration($input);

        if ($this->isLoggingEnabled()) {
            $this->removeOldLogFiles();
        } else {
            $output->writeln('<error>No log file is specified. Detailed results '
                . 'will not be available.</error>');
            $output->write(PHP_EOL);
        }

        if ($input->getOption('incremental')) {
            $output->writeln('<error>Incremental Analysis is an experimental feature and will very likely</error>');
            $output->writeln('<error>yield inaccurate results at this time.</error>');
            $output->write(PHP_EOL);
        }

        if ($this->textLogFile) {
            $renderer = new Text($output, true);
        } else {
            $renderer = new Text($output);
        }

        /**
         * Make initial test run to ensure tests are in a starting passing state
         * and also log the results so test runs during the mutation phase can
         * be optimised.
         */
        $testSuiteRunner = new UnitTestRunner(
            $container->getAdapter(),
            $container->getAdapter()->getProcess($container, true),
            $container->getTempDirectory() . '/coverage.humbug.txt'
        );

        $testSuiteRunner->addObserver(
            new LoggingObserver(
                $renderer,
                $output,
                new ProgressBarObserver($input, $output)
            )
        );

        $result = $testSuiteRunner->run($container);

        /**
         * Check if the initial test run ended with a fatal error
         */
        if (! $result->isSuccess()) {
            return 1;
        }

        $output->write(PHP_EOL);

        /**
         * Message re Static Analysis
         */
        $renderer->renderStaticAnalysisStart();
        $output->write(PHP_EOL);

        $incrementalCache = null;
        if ($input->getOption('incremental')) {
            $incrementalCache = new IncrementalCache($container);
        }

        $mutationTestingRunner = $this->builder->build($container, $renderer, $input, $output);
        $mutationTestingRunner->run($result->getCoverage(), $this->mutableIterator, $incrementalCache);

        if ($this->isLoggingEnabled()) {
            $output->write(PHP_EOL);
        }

        if ($input->getOption('incremental')) {
            $incrementalCache->write();
        }
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

        $this->container->setSourceList($source);
        $timeout = $newConfig->getTimeout();
        if ($timeout !== null) {
            $this->container->setTimeout((int) $timeout);
        }
        $chDir = $newConfig->getChDir();
        if ($chDir !== null) {
            $this->container->setTestRunDirectory($chDir);
        }
        $this->jsonLogFile = $input->getOption('log-json') ?: $newConfig->getLogsJson();
        $this->textLogFile = $input->getOption('log-text') ?: $newConfig->getLogsText();

        $this->builder = new MutantBuilder();
        $this->builder->setLogFiles($this->textLogFile, $this->jsonLogFile);

        $finder = $this->prepareFinder(
            isset($source->directories)? $source->directories : null,
            isset($source->excludes)? $source->excludes : null,
            $input->getOption('file')
        );

        $this->mutableIterator = new MutableIterator($this->container, $finder);
        
        $configure = $newConfig->getConfigure();
        if ($configure !== null) {
            $this->container->setConfigure($configure);
        }
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
            ->addOption(
                'log-json',
                null,
                InputOption::VALUE_REQUIRED,
                'Generate log file in JSON format.'
            )
            ->addOption(
                'log-text',
                null,
                InputOption::VALUE_REQUIRED,
                'Generate log file in text format.'
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
