<?php

namespace Humbug\Test\Command;

use Humbug\Command\Configure;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configure
     */
    private $command;

    /**
     * @var string
     */
    private $configureDir;

    protected function setUp()
    {
        $this->configureDir = sys_get_temp_dir() . '/configure-test-dir-' . rand(10000, 99999);

        mkdir($this->configureDir, 0777, true);
        chdir($this->configureDir);

        $this->command = $this->createConfigureCommand();
    }

    protected function tearDown()
    {
        @unlink('humbug.json');
        @unlink('phpunit.xml');
        @unlink('app/phpunit.xml');

        @rmdir('app');
        @rmdir('src');
        @rmdir('src1');
        @rmdir('src2');

        rmdir($this->configureDir);
    }

    public function testShouldCreateConfigurationIfUserConfirmsIt()
    {
        $srcDirName = 'src';
        mkdir($srcDirName);
        touch('phpunit.xml');

        $this->setUserInput(
            $srcDirName . "\n" .
            "\n" .
            "\n" .
            "\n" .
            "\n" .
            "Y\n"
        );

        $this->executeCommand();

        $expectedJson = <<<JSON
{
    "source": {
        "directories": [
            "src"
        ]
    },
    "timeout": 10,
    "logs": {
        "text": "humbuglog.txt"
    }
}
JSON;

        $this->assertHumbugJsonEqualsJson($expectedJson);
    }

    private function assertHumbugJsonEqualsJson($expectedJson)
    {
        $this->assertJsonStringEqualsJsonString($expectedJson, file_get_contents('humbug.json'));
    }

    public function testShouldCreateConfigurationWithDifferentLocationOfFrameworkConfigFile()
    {
        mkdir('app');
        mkdir('src');
        touch('app/phpunit.xml');

        $this->setUserInput(
            "app\n" .
            "src\n" .
            "\n" .
            "\n" .
            "\n" .
            "\n" .
            "Y\n"
        );

        $this->executeCommand();

        $expectedJson = <<<JSON
{
    "chdir": "app",
    "source": {
        "directories": [
            "src"
        ]
    },
    "timeout": 10,
    "logs": {
        "text": "humbuglog.txt"
    }
}
JSON;

        $this->assertHumbugJsonEqualsJson($expectedJson);
    }

    public function testShouldCreateConfigurationWithCustomTextLogging()
    {
        mkdir('src');
        touch('phpunit.xml');

        $this->setUserInput(
            "src\n" .
            "\n" .
            "\n" .
            "\n" .
            "custom-log.txt\n" .
            "Y\n"
        );

        $this->executeCommand();

        $expectedJson = <<<JSON
{
    "source": {
        "directories": [
            "src"
        ]
    },
    "timeout": 10,
    "logs": {
        "text": "custom-log.txt"
    }
}
JSON;

        $this->assertHumbugJsonEqualsJson($expectedJson);
    }

    public function testShouldCreateConfigurationWithMultipleSourceDirectories()
    {
        $srcDir1 = 'src1';
        mkdir($srcDir1);
        $srcDir2 = 'src2';
        mkdir($srcDir2);
        touch('phpunit.xml');

        $this->setUserInput(
            $srcDir1 . "\n" .
            $srcDir2 . "\n" .
            "\n" .
            "\n" .
            "\n" .
            "\n" .
            "Y\n"
        );

        $this->executeCommand();

        $expectedJson = <<<JSON
{
    "source": {
        "directories": [
            "src1",
            "src2"
        ]
    },
    "timeout": 10,
    "logs": {
        "text": "humbuglog.txt"
    }
}
JSON;

        $this->assertHumbugJsonEqualsJson($expectedJson);
    }

    public function testShouldCreateConfigurationWithExcludeDirectories()
    {
        $srcDir = 'src';
        mkdir($srcDir);

        $excludeDir1 = 'src1';
        mkdir($excludeDir1);

        $excludeDir2 = 'src2';
        mkdir($excludeDir2);

        touch('phpunit.xml');

        $this->setUserInput(
            $srcDir . "\n" .
            "\n" .
            $excludeDir1 . "\n" .
            $excludeDir2 . "\n" .
            "\n" .
            "\n" .
            "\n" .
            "Y\n"
        );

        $this->executeCommand();

        $expectedJson = <<<JSON
{
    "source": {
        "directories": [
            "src"
        ],
        "excludes": [
            "src1",
            "src2"
        ]
    },
    "timeout": 10,
    "logs": {
        "text": "humbuglog.txt"
    }
}
JSON;

        $this->assertHumbugJsonEqualsJson($expectedJson);
    }

    public function testShouldCreateConfigurationWithTimeout()
    {
        mkdir('src');
        touch('phpunit.xml');

        $this->setUserInput(
            "src\n" .
            "\n" .
            "\n" .
            "5\n" .
            "\n" .
            "Y\n"
        );

        $this->executeCommand();

        $expectedJson = <<<JSON
{
    "source": {
        "directories": [
            "src"
        ]
    },
    "timeout": 5,
    "logs": {
        "text": "humbuglog.txt"
    }
}
JSON;

        $this->assertHumbugJsonEqualsJson($expectedJson);
    }

    public function testShouldNotCreateConfigurationIfSrcDirectoryNotExists()
    {
        touch('phpunit.xml');

        $this->setUserInput(
            "Y\n" .
            "not-exists\n" .
            "\n"
        );

        $this->executeCommand();

        $this->assertFileNotExists('humbug.json');
    }

    public function testShouldExitIfIfUserDoesNotWantToConfigure()
    {
        $srcDirName = 'src';
        mkdir($srcDirName);
        touch('phpunit.xml');

        $this->setUserInput(
            $srcDirName . "\n".
            "\n" .
            "\n" .
            "\n" .
            "\n" .
            "N\\n"
        );

        $this->executeCommand();

        $this->assertFileNotExists('humbug.json');
    }

    public function testShouldNotChangeConfigurationIfItAlreadyExists()
    {
        file_put_contents('humbug.json', '{}');

        $this->executeCommand();

        $this->assertStringEqualsFile('humbug.json', '{}');
    }

    private function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

    /**
     * @return Configure
     */
    protected function createConfigureCommand()
    {
        $configure = new Configure();
        $application = new Application();

        $configure->setApplication($application);
        return $configure;
    }

    private function setUserInput($userInput)
    {
        $helper = $this->command->getHelper('question');
        $helper->setInputStream($this->getInputStream($userInput));
    }

    /**
     * @return CommandTester
     */
    private function executeCommand()
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([]);

        return $commandTester;
    }
}
