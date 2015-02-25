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

        $version = humbug_get_contents(self::VERSION);
        if (empty($version)) {
            $output->writeln('Incorrect version check response received. Please try again.');
            return 1;
        }
        if (!preg_match('%^[a-z0-9]{40}%', $version, $matches)) {
            $output->writeln('Unexpected version format received. Please try again.');
            return 1;
        }
        $newVersion = $matches[0];

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
        $tmpFile = $tmpDir . '/' . basename($localFile, '.phar') . '.phar.temp';
        $tmpPubKey = $tmpDir . '/' . basename($localFile, '.phar') . '.phar.temp.pubkey';
        $localPubKey = $localFile . '.pubkey';

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
        if (!file_exists($localPubKey)) {
            throw new FilesystemException(
                'Unable to locate matching public key for this version'
            );
        }
        
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
            $tmpVersion = sha1_file($tmpFile);
            if ($tmpVersion !== $newVersion) {
                @unlink($tmpFile);
                $output->writeln('Downloaded file was corrupted. SHA-1 version hash does not match file.');
                $output->writeln('Please try again.');
                $output->writeln('Expected SHA-1: ' . $newVersion);
                $output->writeln('Received SHA-1: ' . $tmpVersion);
                return 1;
            }
        } catch (\Exception $e) {
            @unlink($tmpFile);
            if ($e instanceof FilesystemException) {
                throw $e;
            }
            $this->writeln('Attempted download from remote URL failed: ' . self::PHAR);
            return 1;
        }

        try {
            @copy($localPubKey, $tmpPubKey);
            @chmod($tmpFile, fileperms($localFile));
            if (!ini_get('phar.readonly')) {
                $phar = new \Phar($tmpFile);
                unset($phar);
            }
            @unlink($tmpPubKey);
            $backupFile = sprintf(
                'humbug-%s.phar.old',
                $oldVersion
            );
            @copy($localFile, dirname($localFile) . '/' . $backupFile);
            rename($tmpFile, $localFile);
        } catch (\Exception $e) {
            @unlink($backupFile);
            @unlink($tmpFile);
            if (!$e instanceof \UnexpectedValueException) {
                throw $e;
            }
            if ($e instanceof \UnexpectedValueException) {
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
