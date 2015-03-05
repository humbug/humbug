<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Finder\Finder;
use Humbug\Exception\FilesystemException;
use Humbug\SelfUpdate\Updater;

class SelfUpdate extends Command
{

    const VERSION = 'https://padraic.github.io/humbug/downloads/humbug.version';

    const PHAR = 'https://padraic.github.io/humbug/downloads/humbug.phar';

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $updater = new Updater;
        $updater->setPharUrl(self::PHAR);
        $updater->setVersionUrl(self::VERSION);
        try {
            $result = $updater->update();
            if ($result) {
                $output->writeln('Humbug has been updated.');
                $output->writeln(sprintf(
                    '<fg=green>Current SHA-1 hash is:</fg=green> <options=bold>%s</options=bold>.',
                    $updater->getNewVersion()
                ));
                $output->writeln(sprintf(
                    '<fg=green>Previous SHA-1 hash was:</fg=green> <options=bold>%s</options=bold>.',
                    $updater->getOldVersion()
                ));
            } else {
                $output->writeln('Humbug is currently up to date.');
                $output->writeln(sprintf(
                    '<fg=green>Current SHA-1 hash is:</fg=green> <options=bold>%s</options=bold>.',
                    $updater->getOldVersion()
                ));
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: <fg=yellow>%s</fg=yellow>', $e->getMessage()));
        }
    }

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Update humbug.phar to current version')
        ;
    }
}
