<?php
/**
 * CLI interface to parse humbuglog.json
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Command;

use Humbug\Log\JsonLogParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Stats extends Command
{
    const DIFF_PLUS = '+';
    const DIFF_MINUS = '-';

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /** @var JsonLogParser */
    private $parser;

    /** @var null|string */
    private $listPath = null;

    /** @var null|string */
    private $logPath = null;

    protected function configure()
    {
        $this
            ->setName('stats')
            ->setDescription('Getting statistics from Humbug logs')
            ->addArgument(
                'log',
                InputArgument::OPTIONAL,
                'Humbug JSON log location',
                'humbuglog.json'
            )
            ->addArgument(
                'classes',
                InputArgument::OPTIONAL,
                'Class list path'
            )
            ->addOption(
                'skip-killed',
                null,
                InputArgument::OPTIONAL,
                'Skip "killed" section',
                'no'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (!$this->checkInputParameters()) {
            return 1;
        }

        $this->initParser();

        $this->processParsing();

        return 0;
    }

    private function checkInputParameters()
    {
        $this->logPath = $this->input->getArgument('log');

        if (!file_exists($this->logPath)) {
            $this->output->writeln("<error>Error opening humbug log: {$this->listPath}</error>");
            return false;
        }
        $this->listPath = $this->input->getArgument('classes');

        return true;
    }

    protected function initParser()
    {
        $this->parser = new JsonLogParser();
        $this->parser->setData(
            json_decode(
                file_get_contents($this->logPath),
                true
            )
        );

        if ($this->listPath) {
            $this->parser->setClassList(
                preg_split(
                    '/[\n\r]+/',
                    trim(file_get_contents($this->listPath))
                )
            );
        }
    }

    protected function processParsing()
    {
        $verbosityLevel = $this->output->getVerbosity();

        $shouldBeVerbose = $verbosityLevel > OutputInterface::VERBOSITY_QUIET;
        $stats = $this->parser->getFilteredStats($shouldBeVerbose);
        $sections = $this->parser->getSectionsList();

        if ($this->input->getOption('skip-killed') === 'yes') {
            unset($stats['killed']);
            unset($sections[0]);
        }

        foreach ($sections as $section) {
            if (!$stats['total'][$section]) {
                continue;
            }

            $this->printSectionHeaderColor($section);
            foreach ($stats[$section] as $class => $data) {
                $this->printSectionData($class, $data, $verbosityLevel);
            }
            $this->output->writeln('');
        }
    }

    private function printSectionHeaderColor($sectionName)
    {
        switch ($sectionName) {
            case 'killed':
                $pattern = '<fg=green>%s</fg=green>';
                break;
            case 'errored':
                $pattern = '<fg=red>%s</fg=red>';
                break;
            case 'escaped':
                $pattern = '<fg=yellow>%s</fg=yellow>';
                break;
            case 'timeouts':
                $pattern = '<fg=cyan>%s</fg=cyan>';
                break;
            default:
                $pattern = '%s';
        }

        $this->output->writeln(sprintf($pattern, str_repeat('=', 20)));
        $this->output->writeln(' ' . sprintf($pattern, ucfirst($sectionName)));
        $this->output->writeln(sprintf($pattern, str_repeat('=', 20)));
    }

    /**
     * @param string $class
     * @param array $data
     * @param $verbosityLevel
     */
    protected function printSectionData($class, $data, $verbosityLevel)
    {
        $this->output->writeln(
            sprintf(
                "%-70s    <info>%d</info>",
                $class,
                $data['count']
            )
        );

        if ($verbosityLevel < OutputInterface::VERBOSITY_VERBOSE || count($data['items']) <= 0) {
            return;
        }

        foreach ($data['items'] as $item) {
            $this->output->writeln(
                sprintf(
                    '    %s(): <comment>%d</comment>',
                    $item['method'],
                    $item['line']
                )
            );

            if ($verbosityLevel < OutputInterface::VERBOSITY_VERY_VERBOSE) {
                continue;
            }

            if ($verbosityLevel === OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $diff = $this->parseDiffShort($item['diff']);
                $pad = '        ';
            } else {
                $diff = $this->parseDiffFull($item['diff'], $item['line']);
                $pad = '';
            }

            $this->output->writeln($pad . $diff);
        }

        $this->output->writeln('');
    }

    private function parseDiffFull($diff, $diffLine)
    {
        $diff = preg_split('/[\n\r]+/', $diff);
        $diff = array_slice($diff, 3);
        $new = '';
        $old = '';
        $diffPos = -1;

        foreach ($diff as $lineNum => $line) {
            list($lineSign, $lineClean) = $this->stringSplitByIndex($line, 1);

            if (!in_array($lineSign, array(static::DIFF_PLUS, static::DIFF_MINUS))) {
                continue;
            }

            if ($line[0] === static::DIFF_PLUS) {
                $new = $lineClean;
            } else {
                $old = $lineClean;
                $diffPos = $lineNum;
            }
        }

        $diff[$diffPos] = $this->generateDiffString($old, $new);
        unset($diff[$diffPos + 1]);

        $startLine = $diffLine - $diffPos + 3;
        $tag = ($startLine != $diffLine) ? 'comment' : 'fg=red';

        foreach ($diff as $num => $item) {
            $diff[$num] = sprintf(
                '<%s>%s</%s>:%s',
                $tag,
                $startLine,
                $tag,
                $item
            );
            $startLine++;
        }

        return implode("\n", $diff);
    }

    private function parseDiffShort($diff)
    {
        $diff = preg_split('/[\n\r]+/', $diff);
        $diff = array_slice($diff, 3);
        $new = '';
        $old = '';

        foreach ($diff as $lineNum => $line) {
            list($lineSign, $lineClean) = $this->stringSplitByIndex($line, 1);

            if (strlen($line) === 0) {
                continue;
            }

            if (!in_array($lineSign, array(static::DIFF_PLUS, static::DIFF_MINUS))) {
                unset($diff[$lineNum]);
                continue;
            }

            $lineClean = trim($lineClean);

            if ($line[0] === static::DIFF_PLUS) {
                $new = $lineClean;
            } else {
                $old = $lineClean;
            }
        }

        return $this->generateDiffString($old, $new);
    }

    private function generateDiffString($old, $new)
    {
        $fromStart = strspn($old ^ $new, "\0");
        $fromEnd = strspn(strrev($old) ^ strrev($new), "\0");

        $oldEnd = strlen($old) - $fromEnd;
        $newEnd = strlen($new) - $fromEnd;

        $start = substr($new, 0, $fromStart);
        $end = substr($new, $newEnd);
        $newDiff = substr($new, $fromStart, $newEnd - $fromStart);
        $oldDiff = substr($old, $fromStart, $oldEnd - $fromStart);

        $result = sprintf(
            '%s<fg=red>%s</fg=red><fg=green>%s</fg=green>%s',
            $start,
            $oldDiff,
            $newDiff,
            $end
        );

        return $result;
    }

    /**
     * @param $string
     * @param $index
     * @return array
     */
    private function stringSplitByIndex($string, $index)
    {
        return [
            substr($string, 0, $index),
            substr($string, $index)
        ];
    }
}
