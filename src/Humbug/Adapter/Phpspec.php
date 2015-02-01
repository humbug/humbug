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
use Humbug\Adapter\Phpspec\YamlConfiguration;
use Humbug\Adapter\Phpspec\Job;
use Humbug\Utility\SpecMapData;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Console\Input\ArgvInput;
use PhpSpec\Console\Application as PhpspecApplication;

class Phpspec extends AdapterAbstract
{

    /**
     * Runs the tests suite according to Runner set options and the execution
     * order of test case (if any). It then returns an array of two elements.
     * First element is a boolean result value indicating if tests passed or not.
     * Second element is an array containing the key "stdout" which stores the
     * output from the last test run.
     *
     * @param   \Humbug\container $container
     * @param   bool              $firstRun
     * @param   null|string       $interceptFile
     * @param   null|string       $mutantFile
     * @param   array             $testSuites
     * @return  \Symfony\Component\Process\PhpProcess
     */
    public function getProcess(
        Container $container,
        $firstRun = false,
        $interceptFile = null,
        $mutantFile = null,
        array $testSuites = [])
    {
        $jobopts = [
            'testdir'       => $container->getTestRunDirectory(),
            'basedir'       => $container->getBaseDirectory(),
            'timeout'       => $container->getTimeout(),
            'cachedir'      => $container->getCacheDirectory(),
            'cliopts'       => $container->getAdapterOptions(),
            'constraints'   => $container->getAdapterConstraints()
        ];

        /*
         * We only need a single fail!
         */
        if (!in_array('--stop-on-failure', $jobopts['cliopts'])) {
            array_unshift($jobopts['cliopts'], '--stop-on-failure');
        }

        /**
         * Handle any editing of the configuration
         */
        $configFile = YamlConfiguration::assemble($container, $firstRun, $testSuites);
        foreach ($jobopts['cliopts'] as $key => $value) {
            if ($value == '--config' || $value == '-c') {
                unset($jobopts['cliopts'][$key]);
                unset($jobopts['cliopts'][$key+1]);
            } elseif (preg_match('%\\-\\-config=%', $value)) {
                unset($jobopts['cliopts'][$key]);
            }
        }
        array_unshift($jobopts['cliopts'], '--config=' . $configFile);

        /**
         * Initial command is expected, of course.
         */
        array_unshift($jobopts['cliopts'], 'run');
        array_unshift($jobopts['cliopts'], 'phpspec');

        /**
         * Log the first run so we can analyse test times to make future
         * runs more efficient in terms of deferring slow test classes to last
         */
        $timeout = 0;
        if ($firstRun) {
            if (!empty($jobopts['constraints'])) {
                $jobopts['cliopts'] = array_merge(
                    $jobopts['cliopts'],
                    explode(' ', $jobopts['constraints'])
                );
            }
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
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $executableFinder = new PhpExecutableFinder();
            $php = $executableFinder->find();
            if ($php !== false) {
                $process->setCommandLine('exec '.$php);
            }
        }
        
        $process->setTimeout($timeout);

        return $process;
    }

    /**
     * Return adapter name
     *
     * @return string
     */
    public function getName()
    {
        return 'phpspec';
    }

    /**
     * Executed in a separate process spawned from the execute() method above.
     *
     * @param string $arguments PHP serialised set of arguments to pass to phpspec
     * @return void
     */
    public static function main($arguments)
    {
        $arguments = unserialize(base64_decode($arguments));

        /**
         * Workaround for PhpSpec\Console\ContainerAssembler depending on this
         * though it won't exist in this new process.
         */
        if (!isset($_SERVER['HOME'])) {
            $_SERVER['HOME'] = sys_get_temp_dir();
        }

        /**
         * Switch working directory to tests (if required) and execute the test suite
         */
        $originalWorkingDir = getcwd();
        if (isset($arguments['testdir']) && !empty($arguments['testdir'])) {
            chdir($arguments['testdir']);
        }
        $application = new PhpspecApplication('2.2.x-dev-humbug');
        try {
            $argv = new ArgvInput($arguments['cliopts']);
            $application->run($argv);
            if (getcwd() !== $originalWorkingDir) {
                chdir($originalWorkingDir);
            }
        } catch (\Exception $e) {
            if (getcwd() !== $originalWorkingDir) {
                chdir($originalWorkingDir);
            }
            throw $e;
        }
    }

    /**
     * Load coverage data from and return
     *
     * @return \Humbug\Utility\CoverageData
     */
    public function getSpecMap(Container $container)
    {
        $coverage = new SpecMapData(
            $container->getCacheDirectory() . '/phpspec.specmap.humbug.json'
        );
        return $coverage;
    }
}
