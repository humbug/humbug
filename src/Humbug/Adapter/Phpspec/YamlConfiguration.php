<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter\Phpspec;

use Humbug\Container;
use Humbug\Adapter\ConfigurationAbstract;
use Humbug\Exception\RuntimeException;
use Humbug\Exception\InvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class YamlConfiguration extends ConfigurationAbstract
{

    private static $config;

    private static $container;

    /**
     * Assemble configuration file required for current mutation testing iteration
     * of the underlying tests.
     *
     * @return string
     */
    public static function assemble(Container $container, $firstRun = false, array $testSuites = [])
    {
        self::$config = self::parseConfigurationFile();
        self::$container = $container;
        if (empty(self::$config)) {
            throw new RuntimeException(
                'Unable to locate the phpspec configuration file'
            );
        }

        self::$config['formatter.name'] = 'tap';
        if (!isset(self::$config['extensions']) || !is_array(self::$config['extensions'])) {
            self::$config['extensions'] = [];
        }

        if (true === $firstRun) {
            self::handleSpecMapLogging();
            self::handleTimeCollectorLogging();
        } else {
            self::handleFilteredResourceLoader();
            self::handleFastestSpecFirstFilter();
            self::handleIncludeOnlySpecFilter($testSuites);
        }

        $saveFile = self::$container->getCacheDirectory() . '/phpspec.humbug.yml';
        $extensions = self::$config['extensions'];

        /**
         * Make extensions the final element to guarantee config is available
         */
        unset(self::$config['extensions']);
        self::$config['extensions'] = $extensions;
        $yaml = Yaml::dump(self::$config, 5);
        file_put_contents($saveFile, $yaml);
        return $saveFile;
    }

    protected static function handleFilteredResourceLoader()
    {
        self::$config['extensions'][] = 'Humbug\PhpSpec\FilteredResourceLoaderExtension';
        if (!isset(self::$config['humbug.filtered_resource_loader.filters'])
        || !is_array(self::$config['humbug.filtered_resource_loader.filters'])) {
            self::$config['humbug.filtered_resource_loader.filters'] = [];
        }
    }

    protected static function handleFastestSpecFirstFilter()
    {
        self::$config['humbug.filtered_resource_loader.filters'][] =
            'Humbug\PhpSpec\Loader\Filter\Specification\FastestFirstFilter';
        self::$config['humbug.time_collector.target'] =
            self::$container->getCacheDirectory() . '/phpspec.times.humbug.json';
    }

    protected static function handleIncludeOnlySpecFilter(array $specs)
    {

    }

    protected static function handleSpecMapLogging()
    {
        self::$config['extensions'][] = 'Humbug\PhpSpec\SpecMapperExtension';
        self::$config['humbug.spec_mapper.target'] =
            self::$container->getCacheDirectory() . '/phpspec.specmap.humbug.json';
    }

    protected static function handleTimeCollectorLogging()
    {
        self::$config['extensions'][] = 'Humbug\PhpSpec\TimeCollectorExtension';
        self::$config['humbug.time_collector.target'] =
            self::$container->getCacheDirectory() . '/phpspec.times.humbug.json';
    }

    protected static function parseConfigurationFile()
    {
        $paths = array('phpspec.yml','phpspec.yml.dist');

        $config = array();
        foreach ($paths as $path) {
            if ($path && file_exists($path) && $parsedConfig = Yaml::parse(file_get_contents($path))) {
                $config = $parsedConfig;
                break;
            }
        }

        if ($homeFolder = getenv('HOME')) {
            $localPath = $homeFolder.'/.phpspec.yml';
            if (file_exists($localPath) && $parsedConfig = Yaml::parse(file_get_contents($localPath))) {
                $config = array_replace_recursive($parsedConfig, $config);
            }
        }

        return $config;
    }

    /**
     * This can set up code coverage, but its usefulness is only as good as the
     * Code Coverage extension and specs, and results have been less than desireable.
     *
     * It has been replaced by handleSpecMapLogging() which uses a simple class->spec
     * mapping.
     *
     * @deprecated
     */
    protected static function handleCodeCoverageLogging()
    {
        if (!isset(self::$config['code_coverage'])
        || !is_array(self::$config['code_coverage'])) {
            self::$config['code_coverage'] = [];
        }
        if (!isset(self::$config['code_coverage']['whitelist'])
        || !is_array(self::$config['code_coverage']['whitelist'])) {
            self::$config['code_coverage']['whitelist'] = [];
        }
        self::$config['extensions'][] = 'PhpSpec\Extension\CodeCoverageExtension';
        self::$config['code_coverage']['format'] = ['text', 'php'];
        self::$config['code_coverage']['output'] = [
            'php'   => self::$container->getCacheDirectory() . '/coverage.humbug.php',
            'text'   => self::$container->getCacheDirectory() . '/coverage.humbug.txt'
        ];

        self::$config['code_coverage']['whitelist'] = [];

        $source = self::$container->getSourceList();
        if (isset($source->directories)) {
            foreach ($source->directories as $d) {
                self::$config['code_coverage']['whitelist'][] = realpath($d);
            }
        }

        if (isset($source->excludes)) {
            if (!isset(self::$config['code_coverage']['blacklist'])
            || !is_array(self::$config['code_coverage']['blacklist'])) {
                self::$config['code_coverage']['blacklist'] = [];
            }
            foreach ($source->excludes as $d) {
                self::$config['code_coverage']['blacklist'][] = realpath($d);
            }
        }

        self::$config['code_coverage']['show_uncovered_files'] = false;
    }
}
