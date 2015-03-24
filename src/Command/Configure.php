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

namespace Humbug\Command;

use Humbug\Adapter\Phpunit\ConfigurationLocator;
use Humbug\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Configure extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            '<fg=green>Humbug configuration tool.' . PHP_EOL .
            'It will guide you through Humbug configuration in few seconds.</fg=green>' . PHP_EOL
        );

        if ($this->isAlreadyConfigured($input)) {
            $output->writeln(
                '<bg=red>Humbug configuration file "humbug.json.dist" already exists. '
                . 'You may use the --force option to start over.</bg=red>' . PHP_EOL
            );
            return 0;
        }

        $output->writeln('When choosing directories, you may enter each directory and press return.');
        $output->writeln('To exit directory selection, please leave the next answer blank and press return.');
        $output->write(PHP_EOL);

        $chDir = $this->resolveChDir($input, $output);

        $sourcesDirs = $this->getDirs($input, $output, 'What source directories do you want to include? : ');

        if (empty($sourcesDirs)) {
            $output->writeln('A source directory was not provided. Unable to generate "humbug.json.dist".');
            return 0;
        }

        $excludeDirs = $this->getDirs($input, $output,
            'Any directories to exclude from within your source directories? :');

        $timeout = $this->getTimeout($input, $output);

        $textLogFile = $this->getTextLogFile($input, $output);

        $jsonLogFile = $this->getJsonLogFile($input, $output);

        if (!$this->isGenerationConfirmed($input, $output)) {
            $output->writeln('<fg=red>Aborted.</fg=red>' .PHP_EOL);
            return 0;
        }

        $configuration = $this->createConfiguration(
            $sourcesDirs,
            $excludeDirs,
            $chDir,
            $timeout,
            $textLogFile,
            $jsonLogFile
        );

        $this->saveConfiguration($configuration);

        $output->writeln('Configuration file "humbug.json.dist" was created.');
    }

    protected function configure()
    {
        $this->setName('configure')
            ->addOption(
               'force',
               'f',
               InputOption::VALUE_NONE,
               'If it already exists, recreate the configuration anyway.'
            )
        ;
    }

    /**
     * @return QuestionHelper
     */
    private function getQuestionHelper()
    {
        return $this->getHelper('question');
    }

    private function isAlreadyConfigured(InputInterface $input)
    {
        if ($input->getOption('force')) {
            return false;
        }
        return file_exists('humbug.json.dist') || file_exists('humbug.json');
    }

    /**
     * @param $sourcesDirs
     * @param $excludeDirs
     * @param $chDir
     * @param $timeout
     * @param $textLogFile
     * @param $jsonLogFile
     *
     * @return \stdClass
     */
    private function createConfiguration(
        $sourcesDirs,
        $excludeDirs,
        $chDir,
        $timeout,
        $textLogFile,
        $jsonLogFile
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

        $logs = $this->prepareLogs($textLogFile, $jsonLogFile);

        if ($logs) {
            $configuration->logs = $logs;
        }

        return $configuration;
    }

    /**
     * @param $configuration
     */
    private function saveConfiguration($configuration)
    {
        file_put_contents('humbug.json.dist', json_encode($configuration, JSON_PRETTY_PRINT));
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
            $output->writeln($e->getMessage());
            $frameworkConfigurationQuestion = $this->createFrameworkConfigurationQuestion($configurationLocator);
            $chDir = $this->getQuestionHelper()->ask($input, $output, $frameworkConfigurationQuestion);

            if (!$chDir) {
                throw new RuntimeException('Could not create "humbug.json.dist". Cannot locate phpunit.xml(.dist) file.');
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
        $frameworkConfigurationQuestion = new Question('Where is your phpunit.xml(.dist) configuration located? : ');
        $frameworkConfigurationQuestion->setValidator(function ($answer) use ($configurationLocator) {

            $answer = trim($answer);

            if (!$answer) {
                return $answer;
            }

            if (!is_dir($answer)) {
                throw new RuntimeException(sprintf('Could not find "%s" directory.', $answer));
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
                throw new RuntimeException(sprintf('Could not find "%s" directory.', $answer));
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
        $timeoutQuestion = new Question('Single test suite timeout in seconds [10] : ', 10);
        $timeoutQuestion->setValidator(function ($answer) {
            if (!$answer || !is_numeric($answer) || (int) $answer <= 0) {
                throw new RuntimeException('Timeout should be an integer greater than 0');
            }

            return (int)$answer;
        });
        return $timeoutQuestion;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function getTextLogFile(InputInterface $input, OutputInterface $output)
    {
        $textLogQuestion = new Question('Where do you want to store the text log? [humbuglog.txt] : ', 'humbuglog.txt');
        $textLogFile = $this->getQuestionHelper()->ask($input, $output, $textLogQuestion);
        return $textLogFile;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function getJsonLogFile(InputInterface $input, OutputInterface $output)
    {
        $textLogQuestion = new Question('Where do you want to store the json log (if you need it)? : ');
        $textLogFile = $this->getQuestionHelper()->ask($input, $output, $textLogQuestion);
        return $textLogFile;
    }

    /**
     * @param $textLogFile
     * @param $jsonLogFile
     * @return null|\stdClass
     */
    private function prepareLogs($textLogFile, $jsonLogFile)
    {
        $logs = null;

        if ($textLogFile || $jsonLogFile) {
            $logs = new \stdClass();

            if ($textLogFile) {
                $logs->text = $textLogFile;
            }

            if ($jsonLogFile) {
                $logs->json = $jsonLogFile;
            }
        }

        return $logs;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function isGenerationConfirmed(InputInterface $input, OutputInterface $output)
    {
        $question = new ConfirmationQuestion('Generate "humbug.json.dist"? [Y]: ', true);
        $generationConfirmed = $this->getQuestionHelper()->ask($input, $output, $question);
        return $generationConfirmed;
    }
}
