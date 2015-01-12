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
use Humbug\Utility\Job;
use Humbug\Utility\Process;
use Humbug\Utility\TestTimeAnalyser;
use Humbug\Utility\CoverageData;
use Humbug\Exception\InvalidArgumentException;
use Humbug\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\PhpProcess;

class Phpunit extends AdapterAbstract
{

    protected static $optimisedConfigFile;

    /**
     * Runs the tests suite according to Runner set options and the execution
     * order of test case (if any). It then returns an array of two elements.
     * First element is a boolean result value indicating if tests passed or not.
     * Second element is an array containing the key "stdout" which stores the
     * output from the last test run.
     *
     * @param   \Humbug\container $container
     * @param   bool              $useStdout
     * @param   bool              $firstRun
     * @param   array             $mutation
     * @param   array             $testCases
     * @return  array
     */
    public function runTests(Container $container, $useStdout = true,
    $firstRun = false, $mutantFile = null, array $testCases = [],
    $interceptFile = null)
    {

        $jobopts = [
            'testdir'       => $container->getTestRunDirectory(),
            'basedir'       => $container->getBaseDirectory(),
            'timeout'       => $container->getTimeout(),
            'cachedir'      => $container->getCacheDirectory(),
            'cliopts'       => $container->getAdapterOptions(),
            'constraints'   => $container->getAdapterConstraints()
        ];

        if(!$useStdout) {
            array_unshift($jobopts['cliopts'], '--stderr');
        }
        
        array_unshift($jobopts['cliopts'], '--tap');

        /*
         * We only need a single fail!
         */
        if (!in_array('--stop-on-failure', $jobopts['cliopts'])) {
            array_unshift($jobopts['cliopts'], '--stop-on-failure');
        }

        /**
         * Setup a PHPUnit XML config file for the purposes of explicitly setting
         * test case order (this will preserve anything else from the original)
         *
         * On first runs we log to junit XML so we can sort tests by performance.
         *
         * TODO: Assemble config just once if no coverage data available!
         */
        $configFile = null;
        if (count($testCases) > 0) {
            $configFile = self::assembleConfiguration($container, $testCases);
        } elseif ($firstRun) {
            $configFile = self::assembleConfiguration(
                $container,
                [],
                $container->getCacheDirectory() . '/junitlog.humbug.xml',
                true
            );
            $coverageFile = $container->getCacheDirectory() . '/coverage.humbug.php';
            array_unshift($jobopts['cliopts'], $coverageFile);
            array_unshift($jobopts['cliopts'], '--coverage-php');
        }
        if (!is_null($configFile)) {
            foreach ($jobopts['cliopts'] as $key => $value) {
                if ($value == '--configuration' || $value == '-C') {
                    unset($jobopts['cliopts'][$key]);
                    unset($jobopts['cliopts'][$key+1]);
                }
            }
            array_unshift($jobopts['cliopts'], $configFile);
            array_unshift($jobopts['cliopts'], '--configuration');
        }

        /**
         * Initial command is expected, of course.
         */
        array_unshift($jobopts['cliopts'], 'phpunit');

        /**
         * Log the first run so we can analyse test times to make future
         * runs more efficient in terms of deferring slow test classes to last
         */
        $timeout = 0;
        if ($firstRun) {
            $jobopts['cliopts'] = array_merge(
                $jobopts['cliopts'],
                explode(' ', $jobopts['constraints'])
            );
        } else {
            $timeout = $container->getTimeout();
        }

        $job = Job::generate(
            $mutantFile,
            $jobopts,
            $container->getBootstrap(),
            $interceptFile
        );

        $process = new PhpProcess($job, null, $_ENV);
        $process->setTimeout($timeout);

        return $process;
    }

    /**
     * Executed in a separate process spawned from the execute() method above.
     *
     * Uses an instance of PHPUnit_TextUI_Command to execute the PHPUnit
     * tests and simulate any Humbug supported command line options suitable
     * for PHPUnit. At present, we merely dissect a generic 'options' string
     * equivalant to anything typed into a console after a normal 'phpunit'
     * command. The adapter captures the TextUI output for further processing.
     *
     * @param string $arguments PHP serialised set of arguments to pass to PHPUnit
     * @return void
     */
    public static function main($arguments)
    {

        $arguments = unserialize(base64_decode($arguments));

        /**
         * Switch working directory to tests (if required) and execute the test suite
         */
        $originalWorkingDir = getcwd();
        if (isset($arguments['testdir']) && !empty($arguments['testdir'])) {
            chdir($arguments['testdir']);
        }
        $command = new \PHPUnit_TextUI_Command;
        try {
            $command->run($arguments['cliopts'], false);
            if (getcwd() !== $originalWorkingDir) chdir($originalWorkingDir);
        } catch (\Exception $e) {
            if (getcwd() !== $originalWorkingDir) chdir($originalWorkingDir);
            throw $e;
        }
    }

    /**
     * Load coverage data from and return
     *
     * @return \Humbug\Utility\CoverageData
     */
    public function getCoverageData(Container $container, TestTimeAnalyser $analyser)
    {
        $coverage = new CoverageData(
            $container->getCacheDirectory() . '/coverage.humbug.php',
            $analyser
        );
        return $coverage;
    }

    /**
     * Load coverage data from and return
     *
     * @return \Humbug\Utility\TestTimeAnalyser
     */
    public function getLogAnalyser(Container $container)
    {
        $analyser = new TestTimeAnalyser(
            $container->getCacheDirectory() . '/junitlog.humbug.xml'
        );
        return $analyser;
    }

    /**
     * Parse the PHPUnit text result output to see if there were any failures.
     * In the context of mutation testing, a test failure is good (i.e. the
     * mutation was detected by the test suite).
     *
     * This assume the output is in Test Anywhere Protocol (TAP) format.
     *
     * @param string $output
     * @return bool
     */
    public static function processOutput($output)
    {
        if (preg_match("%[\n\r]+not ok \\d+%", $output)) {
            return false;
        }
        return true;
    }

    /**
     * Wrangle XML to create a PHPUnit configuration, based on the original, that
     * allows for more control over what tests are run, allows JUnit logging,
     * and ensures that Code Coverage (for Humbug use) whitelists all of the
     * relevant source code.
     *
     *
     * @return string
     */
    public static function assembleConfiguration(Container $container, array $cases = [], $junitLog = null, $addMissingTests = false)
    {
        $conf = null;
        $dir = null;
        $testDir = $container->getTestRunDirectory();
        if (!empty($testDir)) {
            $dir = $testDir;
            $conf = $dir . '/phpunit.xml';
        } elseif (!file_exists($conf)) {
            $dir = $container->getBaseDirectory();
            $conf = $dir . '/phpunit.xml';
        }
        if (file_exists($conf)) {
            $conf = realpath($conf);
        } elseif (file_exists($conf . '.dist')) {
            $conf = realpath($conf . '.dist');
        } else {
            throw new RuntimeException('Unable to locate phpunit.xml(.dist) file. This is required by Humbug.');
        }
        if (!empty($dir)) {
            $dir .= '/';
        }
        $dom = new \DOMDocument;
        $dom->preserveWhitespace = false;
        $dom->formatOutput = true;
        $dom->loadXML(file_get_contents($conf));

        $root = $dom->documentElement;
        $hasBootstrap = false;
        if ($root->hasAttribute('bootstrap')) {
            $hasBootstrap = true;
            $bootstrap = $root->getAttribute('bootstrap');
            $path = static::makeAbsolutePath($bootstrap, dirname($conf));
            $root->setAttribute('bootstrap', $path);
            $container->setBootstrap($path);
        }
        $root->setAttribute('cacheTokens', 'false');

        $xpath = new \DOMXPath($dom);

        /**
         * On first runs collect a test log and also generate code coverage
         */
        if (!is_null($junitLog)) {
            $logging = $dom->createElement('logging');
            $root->appendChild($logging);
            $log = $dom->createElement('log');
            $log->setAttribute('type', 'junit');
            $log->setAttribute('target', $junitLog);
            $log->setAttribute('logIncompleteSkipped', 'true');
            $logging->appendChild($log);

            /**
             * While we're here, reset code coverage filter to meet the known source
             * code constraints.
             */
            $filters = $xpath->query('/phpunit/filter');
            foreach ($filters as $filter) {
                $root->removeChild($filter);
            }
            $filter = $dom->createElement('filter');
            $whitelist = $dom->createElement('whitelist');
            $root->appendChild($filter);
            $filter->appendChild($whitelist);
            $source = $container->getSourceList();
            if (isset($source->directories)) {
                foreach ($source->directories as $d) {
                    $directory = $dom->createElement('directory', realpath($d));
                    $directory->setAttribute('suffix', '.php');
                    $whitelist->appendChild($directory);
                }
            }
            if (isset($source->excludes)) {
                $exclude = $dom->createElement('exclude');
                foreach ($source->excludes as $d) {
                    $directory = $dom->createElement('directory', realpath($d));
                    $exclude->appendChild($directory);
                }
                $whitelist->appendChild($exclude);
            }
        }

        
        $suites = $xpath->query('/phpunit/testsuites/testsuite');
        foreach ($suites as $suite) {
            foreach ($suite->childNodes as $node) {
                if ($node instanceof \DOMElement
                && ($node->tagName == 'directory'
                || $node->tagName == 'exclude'
                || $node->tagName == 'file')) {
                    if (0 === count(glob($node->nodeValue))) {
                        throw new RuntimeException('Unable to locate file specified in testsuites: ' . $node->nodeValue);
                    }

                    $node->nodeValue = static::makeAbsolutePath($node->nodeValue, dirname($conf));
                }
            }
        }

        $xpath = new \DOMXPath($dom);

        /**
         * Set any remaining file & directory references to realpaths
         */
        $directories = $xpath->query('//directory');
        foreach ($directories as $directory) {
            $directory->nodeValue = static::makeAbsolutePath($directory->nodeValue, dirname($conf));
        }
        $files = $xpath->query('//file');
        foreach ($files as $file) {
            $file->nodeValue = static::makeAbsolutePath($file->nodeValue, dirname($conf));
        }

        if (!empty($cases)) {

            // TODO: Handle >1 test suites
            $suite1 = $xpath->query('/phpunit/testsuites/testsuite')->item(0);
            if (is_a($suite1, 'DOMElement')) {
                foreach ($suite1->childNodes as $child) {
                    // phpunit.xml may omit bootstrap location but grab it automatically - include explicitly
                    if ($child instanceof \DOMElement && $child->tagName == 'directory' && $hasBootstrap === false) {
                        $bootstrapDir = static::makeAbsolutePath($child->nodeValue, dirname($conf));
                        if (file_exists($bootstrapDir . '/bootstrap.php')) {
                            $root->setAttribute('bootstrap', $bootstrapDir . '/bootstrap.php');
                        }
                    }
                    // we only want file references in specific order + excludes (for now, we retain these)
                    if ($child instanceof \DOMElement && $child->tagName !== 'exclude') {
                        $suite1->removeChild($child);
                    }
                }

                /**
                 * Add test files explicitly in order given
                 */
                $files = [];
                foreach ($cases as $case) {
                    $files[] = $case['file'];
                    $file = $dom->createElement('file', $case['file']);
                    $suite1->appendChild($file);
                }
                /**
                 * JUnit logging excludes some immeasurable tests so we'll add those back.
                 */
                if ($addMissingTests) {
                    $finder = new Finder;
                    $finder->name('*Test.php');
                    // TODO: Make sure this only ever includes tests!
                    foreach ($finder->in($container->getBaseDirectory())->exclude('vendor') as $file) {
                        if (!in_array($file->getRealpath(), $files)) {
                            $file = $dom->createElement('file', $file->getRealpath());
                            $suite1->appendChild($file);
                        }
                    }
                }
            }
        } else {
            // TODO: Handle >1 test suites
            $suite1 = $xpath->query('/phpunit/testsuites/testsuite')->item(0);
            if (is_a($suite1, 'DOMElement')) {
                foreach ($suite1->childNodes as $child) {
                    // phpunit.xml may omit bootstrap location but grab it automatically - include explicitly
                    if ($child instanceof \DOMElement && $child->tagName == 'directory' && $hasBootstrap === false) {
                        $bootstrapDir = static::makeAbsolutePath($child->nodeValue, dirname($conf));
                        if (file_exists($bootstrapDir . '/bootstrap.php')) {
                            $root->setAttribute('bootstrap', $bootstrapDir . '/bootstrap.php');
                            $container->setBootstrap($bootstrapDir . '/bootstrap.php');
                            $hasBootstrap = true;
                        }
                    }
                }
            }
        }

        /**
         * Include any listeners
         */
        /*$listeners = $dom->createElement('listeners');
        $root->appendChild($listeners);
        $listener = $dom->createElement('listener');
        $listeners->appendChild($listener);
        $listener->setAttribute('class', '\\MyBuilder\\PhpunitAccelerator\\TestListener');
        $arguments = $dom->createElement('arguments');
        $listener->appendChild($arguments);
        $boolean = $dom->createElement('boolean');
        $arguments->appendChild($boolean);
        $boolean->nodeValue = 'true';*/

        
        $saveFile = $container->getCacheDirectory() . '/phpunit.humbug.xml';
        $dom->save($saveFile);
        return $saveFile;
    }


    private static function makeAbsolutePath($name, $workingDir)
    {
        // @see https://github.com/symfony/Config/blob/master/FileLocator.php#L83
        if ('/' === $name[0] 
            || '\\' === $name[0]
            || (strlen($name) > 3 && ctype_alpha($name[0]) && $name[1] == ':' && ($name[2] == '\\' || $name[2] == '/'))
        ) {
            if (!file_exists($name)) {
                throw new InvalidArgumentException("$name does not exist");
            }

            return realpath($name);
        }

        $relativePath = $workingDir.DIRECTORY_SEPARATOR.$name;
        if (file_exists($relativePath)) {
            return realpath($relativePath);
        }

        throw new InvalidArgumentException("Could not find file $name working from $workingDir");
    }
}
