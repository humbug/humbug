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
        @unlink($this->configureDir . '/humbug.json');
        @rmdir('src');
        rmdir($this->configureDir);
    }

    public function testShouldCreateConfigurationIfUserConfirmsIt()
    {
        $srcDirName = 'src';
        mkdir($srcDirName);

        $this->setUserInput(
            "Y\n" .
            $srcDirName . "\n"
        );

        $commandTester = $this->executeCommand();

        $this->assertStringEndsWith('Configuration file "humbug.json" was created.' . PHP_EOL, $commandTester->getDisplay());
        $this->assertFileExists('humbug.json');

        $expectedJson = <<<JSON
{
    "source": {
        "directories": [
            "src"
        ]
    }
}
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, file_get_contents('humbug.json'));
    }

    public function testShouldExitIfIfUserDoesNotWantToConfigure()
    {
        $this->setUserInput('n\\n');

        $commandTester = $this->executeCommand();

        $this->assertEquals('Do you want to create humbug.json [Y]: ' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testShouldWarnUserThatConfigurationAlreadyExistsAndExit()
    {
        touch('humbug.json');

        $commandTester = $this->executeCommand();

        $this->assertEquals('Humbug humbug.json already exists.' . PHP_EOL, $commandTester->getDisplay());
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