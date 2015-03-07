<?php

namespace Humbug\Command;


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

        $questionHelper = $this->getQuestionHelper();

        $sourceQuestion = $this->createSourceQuestion();
        $sourcesDirs = [];

        while ($sourceDir = $questionHelper->ask($input, $output, $sourceQuestion)) {
            if ($sourceDir) {
                $sourcesDirs[] = $sourceDir;
            }
        }

        if (empty($sourcesDirs)) {
            $output->writeln('Sources directories were not provided. Cannot generate humbug.json');
            return 0;
        }

        $question = new ConfirmationQuestion('Confirm generation of humbug.json [Y]: ', true);

        if (!$questionHelper->ask($input, $output, $question)) {
            $output->writeln('');
            return 0;
        }

        $configuration = $this->createConfiguration($sourcesDirs);

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
     * @return Question
     */
    private function createSourceQuestion()
    {
        $sourceQuestion = new Question('Where your source are located? [Enter = exit] : ');
        $sourceQuestion->setValidator(function ($answer) {
            if (trim($answer) && !is_dir($answer)) {
                throw new \RuntimeException(sprintf('Could not find "%s" directory', $answer));
            }

            return $answer;
        });
        return $sourceQuestion;
    }

    /**
     * @param $sourcesDirs
     * @return \stdClass
     */
    private function createConfiguration($sourcesDirs)
    {
        $source = new \stdClass();
        $source->directories = $sourcesDirs;

        $configuration = new \stdClass();
        $configuration->source = $source;
        return $configuration;
    }

    /**
     * @param $configuration
     */
    private function saveConfiguration($configuration)
    {
        file_put_contents('humbug.json', json_encode($configuration, JSON_PRETTY_PRINT));
    }
} 