<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant;

use Humbug\Container;
use Humbug\Renderer\Text;
use Humbug\TestSuite\Mutant\Observers\JsonLoggingObserver;
use Humbug\TestSuite\Mutant\Observers\LoggingObserver;
use Humbug\TestSuite\Mutant\Observers\PerformanceObserver;
use Humbug\TestSuite\Mutant\Observers\TextLoggingObserver;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Builder
{

    /**
     * @var string
     */
    private $jsonLogFile;

    /**
     * @var string
     */
    private $textLogFile;

    /**
     * @param string|null $textLogFile
     * @param string|null $jsonLogFile
     */
    public function setLogFiles($textLogFile, $jsonLogFile)
    {
        $this->textLogFile = $textLogFile;
        $this->jsonLogFile = $jsonLogFile;
    }

    /**
     * @param Container $container
     * @param Text $renderer
     * @param OutputInterface $output
     *
     * @return Runner
     */
    public function build(Container $container, Text $renderer, OutputInterface $output)
    {
        /**
         * We can do parallel runs, but typically two test runs will compete for
         * any uninsulated resources (e.g. files/database) so hardcoded to 1 for now.
         *
         * TODO: Move PHPUnit specific stuff to adapter...
         */
        $testSuite = new Runner($container, 1);

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
