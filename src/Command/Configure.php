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
        $questionHelper = $this->getQuestionHelper();

        $question = new ConfirmationQuestion('Do you want to create humbug.json [Y]: ', true);

        if (!$questionHelper->ask($input, $output, $question)) {
            $output->writeln('Thats a pity:( Maybe another time');
        }else {
            $output->writeln('So lets configure');
        }
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