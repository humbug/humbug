<?php

/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 *
 * @author     rafal.wartalski@gmail.com
 */

namespace Humbug\Test;

use Humbug\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validSourceDataProvider
     */
    public function testShouldHaveSource($configData)
    {
        $config = new Config($configData);

        $this->assertEquals($configData->source, $config->getSource());
    }

    public function validSourceDataProvider()
    {
        $sourceWithDirectories = (object)[
            'source' => (object)[
                'directories' => []
            ]
        ];

        $sourceWithExcludes = (object)[
            'source' => (object)[
                'excludes' => []
            ]
        ];

        $sourceWithDirectoriesAndExcludes = (object)[
            'source' => (object)[
                'directories' => [],
                'excludes' => []
            ]
        ];

        return [
            [$sourceWithDirectories],
            [$sourceWithExcludes],
            [$sourceWithDirectoriesAndExcludes]
        ];
    }

    public function testShouldRiseExceptionWhenSourceIsNotPresent()
    {
        $configData = new \stdClass();

        $config = new Config($configData);

        $this->setExpectedException(
            'Humbug\Exception\JsonConfigException',
            'Source code data is not included in configuration file'
        );

        $config->getSource();
    }

    public function testShouldRiseExceptionWhenSourceDirectoriesAndExcludesAreNotPresent()
    {
        $configData = (object)[
            'source' => (object)[]
        ];

        $this->setExpectedException(
            'Humbug\Exception\JsonConfigException',
            'You must set at least one source directory or exclude in the configuration file'
        );

        $config = new Config($configData);

        $config->getSource();
    }

    public function testShouldHaveTimeout()
    {
        $configData = (object)[
            'timeout' => 10
        ];

        $config = new Config($configData);

        $this->assertEquals(10, $config->getTimeout());
    }

    public function testShouldHaveEmptyTimeout()
    {
        $configData = new \stdClass();

        $config = new Config($configData);

        $this->assertNull($config->getTimeout());
    }

    public function testShouldHaveChDir()
    {
        $dirPath = __DIR__ . '/_files';
        $configData = (object)[
            'chdir' => $dirPath
        ];

        $config = new Config($configData);

        $this->assertEquals($dirPath, $config->getChDir());
    }

    public function testShouldHaveEmptyChDir()
    {
        $configData = new \stdClass();

        $config = new Config($configData);

        $this->assertNull($config->getChDir());
    }

    public function testShouldRiseExceptionWhenChDirNotExists()
    {
        $configData = (object)[
            'chdir' => 'path/to/not-a-dir'
        ];

        $config = new Config($configData);

        $this->setExpectedExceptionRegExp(
            'Humbug\Exception\JsonConfigException',
            '/Directory in which to run tests does not exist: .+/'
        );

        $config->getChDir();
    }

    public function testShouldHaveLogsJsonAndLogsText()
    {
        $logsJsonFile = __DIR__ . '/_files/logs/test.json';
        $logsTextFile = __DIR__ . '/_files/logs/test.txt';

        $configData = (object)[
            'logs' => (object) [
                'json' => $logsJsonFile,
                'text' => $logsTextFile
            ]
        ];

        $config = new Config($configData);

        $this->assertEquals($logsJsonFile, $config->getLogsJson());
        $this->assertEquals($logsTextFile, $config->getLogsText());
    }

    public function testShouldHaveEmptyLogsJsonAndLogsText()
    {
        $configData = new \stdClass();

        $config = new Config($configData);

        $this->assertNull($config->getLogsJson());
        $this->assertNull($config->getLogsText());
    }

    public function testShouldNotRiseExceptionWhenLogsJsonDirNotExists()
    {
        $directory = 'path/to/not-a-dir';
        $configData = (object)[
            'logs' => (object)[
                'json' => $directory . '/logs.json'
            ]
        ];

        $config = new Config($configData);

        $config->getLogsJson();

        $this->assertTrue(file_exists($directory) && is_dir($directory));

        // Remove the directory so that it does not persist over to other tests.
        $this->assertTrue(rmdir($directory), 'Could not remove test directory. This will affect subsequent tests.');
    }

    public function testShouldNotRiseExceptionWhenTextJsonDirNotExists()
    {
        $directory = 'path/to/not-a-dir';
        $configData = (object)[
            'logs' => (object)[
                'text' => $directory . '/logs.txt'
            ]
        ];

        $config = new Config($configData);

        $config->getLogsText();

        $this->assertTrue(file_exists($directory) && is_dir($directory));

        // Remove the directory so that it does not persist over to other tests.
        $this->assertTrue(rmdir($directory), 'Could not remove test directory. This will affect subsequent tests.');
    }
}
