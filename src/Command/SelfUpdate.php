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
        $localFile = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];

        $version = @humbug_get_contents(self::VERSION);
        if (empty($version) || !preg_match('%^[a-z0-9]{40}\s+humbug\.phar$%', $version)) {
            $output->writeln('Current version check has failed. Please try again.');
            return 1;
        }
        $parts = explode('  ', $version);
        $newVersion = $parts[0];

        $oldVersion = sha1_file($localFile);

        if ($newVersion !== $oldVersion) {
            $this->replacePhar($localFile, $oldVersion, $newVersion, $output);
        } else {
            $output->writeln('Humbug is currently up to date.');
            $output->writeln('Current SHA-1 hash is: ' . $oldVersion . '.');
        }

    }

    private function replacePhar($localFile, $oldVersion, $newVersion, OutputInterface $output)
    {
        
        $tmpDir = dirname($localFile);
        if (!is_writable($tmpDir)) {
            throw new FilesystemException(
                'Directory for file download not writeable: ' . $tmpDir
            );
        }
        if (!is_writable($localFile)) {
            throw new FilesystemException(
                'Current phar file is not writeable and cannot be replaced: ' . $localFile
            );
        }
        $tmpFile = $tmpDir . '/' . basename($localFile, '.phar') . '.phar.temp';
        $output->writeln('Downloading new Humbug version');

        try {
            file_put_contents(
                $tmpFile,
                @humbug_get_contents(self::PHAR)
            );
            if (!file_exists($tmpFile)) {
                throw new FilesystemException(
                    'Download failed for unknown reason'
                );
            }
            if (sha1_file($tmpFile) !== $newVersion) {
                $output->writeln('Downloaded file was corrupted. SHA-1 version hash does not match file.');
                $output->writeln('Please try again.');
                return 1;
            }
        } catch (\Exception $e) {
            if ($e instanceof FilesystemException) {
                throw $e;
            }
            $this->writeln('Attempted download from remote URL failed: ' . self::PHAR);
            return 1;
        }

        try {
            @chmod($tmpFile, fileperms($localFile));
            if (!ini_get('phar.readonly')) {
                $phar = new \Phar($tmpFile);
                unset($phar);
            }
            $backupFile = sprintf(
                'humbug-%s.phar.old',
                $oldVersion
            );
            @copy($localFile, dirname($localFile) . '/' . $backupFile);
            rename($tmpFile, $localFile);
        } catch (\Exception $e) {
            @unlink($backupFile);
            if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                throw $e;
            }
            if ($e instanceof \UnexpectedValueException || $e instanceof \PharException) {
                $output->writeln('Downloaded file was corrupted. Please try again.');
                return 1;
            }
        }

        if (!file_exists(dirname($localFile) . '/' . $backupFile)) {
            $this->writeln('A backup of the original phar file could not be saved.');
        }

        $output->writeln('Humbug has been updated.');
        $output->writeln('Current SHA-1 hash is: ' . $newVersion . '.');
        $output->writeln('Previous SHA-1 hash was: ' . $oldVersion . '.');
    }

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Update humbug.phar to current version')
        ;
    }
}
