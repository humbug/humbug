<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter;

use Humbug\Container;
use Humbug\Adapter\Phpunit\TestClassLocator;
use Humbug\Adapter\Phpunit\XmlConfiguration;
use Humbug\Adapter\Phpunit\XmlConfigurationBuilder;
use Humbug\Adapter\Phpunit\Job;
use Humbug\Adapter\Phpunit\Process\PhpunitExecutableFinder;
use Humbug\Utility\CoverageData;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class Phpunit extends AdapterAbstract
{

    /**
     * @var TestClassLocator
     */
    private $locator;

    /**
     * Runs the tests suite according to Runner set options and the execution
     * order of test case (if any). It then returns an array of two elements.
     * First element is a boolean result value indicating if tests passed or not.
     * Second element is an array containing the key "stdout" which stores the
     * output from the last test run.
     *
     * @param   Container         $container
     * @param   bool              $firstRun
     * @param   null|string       $interceptFile
     * @param   null|string       $mutantFile
     * @param   array             $testSuites
     * @return  Process
     */
    public function getProcess(
        Container $container,
        $firstRun = false,
        $interceptFile = null,
        $mutantFile = null,
        array $testSuites = []
    ) {
        $jobopts = [
            'testdir'       => $container->getTestRunDirectory(),
            'basedir'       => $container->getBaseDirectory(),
            'timeout'       => $container->getTimeout(),
            'cachedir'      => $container->getTempDirectory(),
            'command'       => $container->getAdapterOptions(),
            'constraints'   => $container->getAdapterConstraints()
        ];

        /**
         * We like standardised easy to parse outout
         */
        array_unshift($jobopts['command'], '--teamcity');

        /*
         * We only need a single fail!
         */
        if (!in_array('--stop-on-failure', $jobopts['command'])) {
            array_unshift($jobopts['command'], '--stop-on-failure');
        }

        /**
         * Setup a PHPUnit XML config file for the purposes of explicitly setting
         * test case order (this will preserve anything else from the original)
         *
         * On first runs we log to junit XML so we can sort tests by performance.
         *
         * TODO: Assemble config just once if no coverage data available!
         */
        $xmlConfiguration = $this->assembleXmlConfiguration($container, $firstRun, $testSuites);

        $configFile = $container->getTempDirectory() . '/phpunit.humbug.xml';

        file_put_contents($configFile, $xmlConfiguration->generateXML());

        foreach ($jobopts['command'] as $key => $value) {
            if ($value == '--configuration' || $value == '-C') {
                unset($jobopts['command'][$key]);
                unset($jobopts['command'][$key+1]);
            } elseif (preg_match('%\\-\\-configuration=%', $value)) {
                unset($jobopts['command'][$key]);
            }
        }
        array_unshift($jobopts['command'], '--configuration=' . $configFile);

        /**
         * Initial command is expected, of course.
         */
        $phpunitFinder = new PhpunitExecutableFinder;
        $command = $phpunitFinder->find();
        array_unshift($jobopts['command'], $command);

        /**
         * Log the first run so we can analyse test times to make future
         * runs more efficient in terms of deferring slow test classes to last
         */
        $timeout = 0;
        if ($firstRun) {
            $jobopts['command'] = array_merge(
                $jobopts['command'],
                explode(' ', $jobopts['constraints'])
            );
        } else {
            $timeout = $container->getTimeout();
        }

        Job::generate(
            $mutantFile,
            $container->getBootstrap(),
            $interceptFile
        );
        
        $process = new Process(implode(' ', $jobopts['command']), $jobopts['testdir'], array_replace($_ENV, $_SERVER));
        $process->setTimeout($timeout);

        return $process;
    }

    /**
     * Load coverage data from and return
     *
     * @param Container $container
     * @return \Humbug\Utility\CoverageData
     */
    public function getCoverageData(Container $container)
    {
        $coverage = new CoverageData(
            $container->getTempDirectory() . '/coverage.humbug.php'
        );
        return $coverage;
    }

    public function getClassFile($class, Container $container)
    {
        if (is_null($this->locator)) {
            $this->locator = new TestClassLocator($container);
        }
        return $this->locator->locate($class);
    }

    /**
     * Wrangle XML to create a PHPUnit configuration, based on the original, that
     * allows for more control over what tests are run, allows JUnit logging,
     * and ensures that Code Coverage (for Humbug use) whitelists all of the
     * relevant source code.
     *
     *
     * @return XmlConfiguration
     */
    private function assembleXmlConfiguration(Container $container, $firstRun = false, array $testSuites = [])
    {
        $configurationDir = $this->resolveConfigurationDir($container);
        $xmlConfigurationBuilder = new XmlConfigurationBuilder($configurationDir);

        if ($firstRun) {
            $xmlConfigurationBuilder->setPhpCoverage($container->getTempDirectory() . '/coverage.humbug.php');
            $xmlConfigurationBuilder->setTextCoverage($container->getTempDirectory() . '/coverage.humbug.txt');
            $xmlConfigurationBuilder->setJunitLog($container->getTempDirectory() . '/junit.humbug.xml');
            $whiteListSrc = $this->getWhiteListSrc($container);
            $excludeDirs = $this->getExcludeDirs($container);
            $xmlConfigurationBuilder->setCoverageFilter($whiteListSrc, $excludeDirs);
            $xmlConfigurationBuilder->setTimeCollectionListener($this->getPathToTimeCollectorFile($container));
        } else {
            $xmlConfigurationBuilder->setFilterListener($testSuites, $this->getPathToTimeCollectorFile($container));
        }

        $xmlConfigurationBuilder->setAcceleratorListener();
        $xmlConfiguration = $xmlConfigurationBuilder->getConfiguration();

        if ($xmlConfiguration->hasOriginalBootstrap()) {
            $container->setBootstrap($xmlConfiguration->getOriginalBootstrap());
        }
        return $xmlConfiguration;
    }

    private function getPathToTimeCollectorFile(Container $container)
    {
        return $container->getTempDirectory() . '/phpunit.times.humbug.json';
    }

    /**
     * @param Container $container
     * @return string
     */
    private function resolveConfigurationDir(Container $container)
    {
        $configurationDir = $container->getTestRunDirectory();
        if (empty($configurationDir)) {
            $configurationDir = $container->getBaseDirectory();
        }
        return realpath($configurationDir);
    }

    /**
     * @param Container $container
     * @return array
     */
    private function getWhiteListSrc(Container $container)
    {
        $srcList = $container->getSourceList();
        return isset($srcList->directories) ? $this->getRealPathList($srcList->directories) : [];
    }

    /**
     * @param Container $container
     * @return array
     */
    private function getExcludeDirs(Container $container)
    {
        $srcList = $container->getSourceList();
        return isset($srcList->excludes) ? $this->getRealPathList($srcList->excludes) : [];
    }

    /**
     * @param array $directories
     * @return array
     */
    private function getRealPathList(array $directories)
    {
        return array_map('realpath', $directories);
    }
}
