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

    protected function setUp()
    {
        $this->command = $this->createConfigureCommand();
    }

    public function testShouldCreateConfigurationIfUserConfirmsIt()
    {
        $this->setUserInput('Y\\n');

        $commandTester = $this->executeCommand();

        $this->assertStringEndsWith('So lets configure' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testShouldNotTakeAnyActionIfUserDoesNotWantThem()
    {
        $this->setUserInput('n\\n');

        $commandTester = $this->executeCommand();

        $this->assertStringEndsWith('Thats a pity:( Maybe another time' . PHP_EOL, $commandTester->getDisplay());
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