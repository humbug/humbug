<?php

namespace Humbug\Command;

use Humbug\Adapter\Phpunit\ConfigurationLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Configure extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->isAlreadyConfigured()) {
            $output->writeln('Humbug humbug.json already exists.');
            return 0;
        }

        $chDir = $this->resolveChDir($input, $output);

        $sourcesDirs = $this->getDirs($input, $output, 'Which source dir you want to include? : ');

        if (empty($sourcesDirs)) {
            $output->writeln('Sources directories were not provided. Cannot generate humbug.json');
            return 0;
        }

        $excludeDirs = $this->getDirs($input, $output, 'Which dir you want to exclude? :');

        $timeout = $this->getTimeout($input, $output);

        $textLogQuestion = new Question('Where do you want to store text logs ? [humbuglog.txt] : ', 'humbuglog.txt');

        $textLogFile = $this->getQuestionHelper()->ask($input, $output, $textLogQuestion);

        $question = new ConfirmationQuestion('Confirm generation of humbug.json [Y]: ', true);

        if (!$this->getQuestionHelper()->ask($input, $output, $question)) {
            $output->writeln('');
            return 0;
        }

        $configuration = $this->createConfiguration(
            $sourcesDirs,
            $excludeDirs,
            $chDir,
            $timeout,
            $textLogFile
        );

        $this->saveConfiguration($configuration);

        $output->writeln('Configuration file "humbug.json" was created.');
    }

    protected function configure()
    {
        $this->setName('configure');
    }

    /**
     * @return QuestionHelper
     */
    private function getQuestionHelper()
    {
        return $this->getHelper('question');
    }

    private function isAlreadyConfigured()
    {
        return file_exists('humbug.json');
    }

    /**
     * @param $sourcesDirs
     * @param $excludeDirs
     * @param $chDir
     * @param $timeout
     *
     * @return \stdClass
     */
    private function createConfiguration(
        $sourcesDirs,
        $excludeDirs,
        $chDir,
        $timeout,
        $textLogFile
    ) {
        $source = new \stdClass();
        $source->directories = $sourcesDirs;

        if (!empty($excludeDirs)) {
            $source->excludes = $excludeDirs;
        }

        $configuration = new \stdClass();
        $configuration->source = $source;

        if ($chDir) {
            $configuration->chdir = $chDir;
        }

        if ($timeout) {
            $configuration->timeout = $timeout;
        }

        if ($textLogFile) {
            $logs = new \stdClass();

            $logs->text = $textLogFile;

            $configuration->logs = $logs;
        }

        return $configuration;
    }

    /**
     * @param $configuration
     */
    private function saveConfiguration($configuration)
    {
        file_put_contents('humbug.json', json_encode($configuration, JSON_PRETTY_PRINT));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string|null
     *
     * @throws \RuntimeException
     */
    private function resolveChDir(InputInterface $input, OutputInterface $output)
    {
        $configurationLocator = new ConfigurationLocator();

        try {
            $configurationLocator->locate('.');
            return null;
        } catch (\RuntimeException $e) {
            $chDir = null;

            $output->writeln($e->getMessage());

            $frameworkConfigurationQuestion = $this->createFrameworkConfigurationQuestion($configurationLocator);

            $chDir = $this->getQuestionHelper()->ask($input, $output, $frameworkConfigurationQuestion);

            if (!$chDir) {
                throw new \RuntimeException("Could not create 'humbug.json'. Cannot locate phpunit.xml");
            }

            return $chDir;
        }
    }

    /**
     * @param ConfigurationLocator $configurationLocator
     * @return Question
     */
    private function createFrameworkConfigurationQuestion(ConfigurationLocator $configurationLocator)
    {
        $frameworkConfigurationQuestion = new Question('Where is your phpunit.xml(.dist) configuration? : ');
        $frameworkConfigurationQuestion->setValidator(function ($answer) use ($configurationLocator) {

            $answer = trim($answer);

            if (!$answer) {
                return $answer;
            }

            if (!is_dir($answer)) {
                throw new \RuntimeException(sprintf('Could not find "%s" directory', $answer));
            }

            $configurationLocator->locate($answer);

            return $answer;
        });

        return $frameworkConfigurationQuestion;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    private function getDirs(InputInterface $input, OutputInterface $output, $question)
    {
        $sourcesDirs = [];

        $sourceQuestion = $this->createSourceQuestion($question);

        while ($sourceDir = $this->getQuestionHelper()->ask($input, $output, $sourceQuestion)) {
            if ($sourceDir) {
                $sourcesDirs[] = $sourceDir;
            }
        }

        return $sourcesDirs;
    }

    /**
     * @param $question
     * @return Question
     */
    private function createSourceQuestion($question)
    {
        $sourceQuestion = new Question($question);
        $sourceQuestion->setValidator(function ($answer) {
            if (trim($answer) && !is_dir($answer)) {
                throw new \RuntimeException(sprintf('Could not find "%s" directory', $answer));
            }

            return $answer;
        });
        return $sourceQuestion;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function getTimeout(InputInterface $input, OutputInterface $output)
    {
        $timeoutQuestion = $this->createTimeoutQuestion();

        $timeout = $this->getQuestionHelper()->ask($input, $output, $timeoutQuestion);

        return $timeout;
    }

    /**
     * @return Question
     */
    private function createTimeoutQuestion()
    {
        $timeoutQuestion = new Question('Single test timeout in seconds [10] : ', 10);
        $timeoutQuestion->setValidator(function ($answer) {
            if (!$answer || !is_numeric($answer)) {
                throw new \RuntimeException('Timeout should be number');
            }

            return (int)$answer;
        });
        return $timeoutQuestion;
    }
}
