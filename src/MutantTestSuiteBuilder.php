<?php

namespace Humbug;

use Humbug\MutantObservers\JsonLoggingObserver;
use Humbug\MutantObservers\LoggingObserver;
use Humbug\MutantObservers\PerformanceObserver;
use Humbug\MutantObservers\TextLoggingObserver;
use Humbug\Renderer\Text;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class MutantTestSuiteBuilder
{

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var string
     */
    private $jsonLogFile;

    /**
     * @var string
     */
    private $textLogFile;

    public function __construct($directories, $excludes)
    {
        $this->finder = $this->prepareFinder($directories, $excludes);
    }

    public function setLogFiles($textLogFile, $jsonLogFile)
    {
        $this->textLogFile = $textLogFile;
        $this->jsonLogFile = $jsonLogFile;
    }

    protected function prepareFinder($directories, $excludes)
    {
        $finder = new Finder();
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

    public function build(Container $container, Text $renderer, OutputInterface $output)
    {
        /**
         * Examine all source code files and collect up mutations to apply
         *
         * TODO: Move this out of builder -- somewhere else
         */
        $mutables = $container->getMutableFiles($this->finder);

        /**
         * We can do parallel runs, but typically two test runs will compete for
         * any uninsulated resources (e.g. files/database) so hardcoded to 1 for now.
         *
         * TODO: Move PHPUnit specific stuff to adapter...
         */
        $testSuite = new MutantTestSuite($mutables, 1);

        $testSuite->addObserver(new LoggingObserver($renderer, $output));
        $testSuite->addObserver(new PerformanceObserver($renderer));

        /**
         * Add logging observers
         */
        if ($this->jsonLogFile) {
            $testSuite->addObserver(new JsonLoggingObserver($renderer, $this->jsonLogFile));
        }

        if ($this->textLogFile) {
            $testSuite->addObserver(new TextLoggingObserver($renderer, $this->textLogFile));
        }

        return $testSuite;
    }
}