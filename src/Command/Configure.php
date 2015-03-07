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
        if (file_exists('humbug.json')) {
            $output->writeln('Humbug humbug.json already exists.');
            return 0;
        }

        $questionHelper = $this->getQuestionHelper();

        $question = new ConfirmationQuestion('Do you want to create humbug.json [Y]: ', true);

        if (!$questionHelper->ask($input, $output, $question)) {
            $output->writeln('');
            return;
        }

        $sourceQuestion = new Question('Where your source are located? : ');

        $sourceDir = $questionHelper->ask($input, $output, $sourceQuestion);

        $source = new \stdClass();
        $source->directories = [
            $sourceDir
        ];

        $configuration = new \stdClass();
        $configuration->source = $source;

        file_put_contents('humbug.json', json_encode($configuration, JSON_PRETTY_PRINT));
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
} 