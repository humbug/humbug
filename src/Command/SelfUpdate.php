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

use Humbug\Exception\FilesystemException;
use Humbug\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Humbug\SelfUpdate\Updater;
use Humbug\SelfUpdate\VersionParser;

class SelfUpdate extends Command
{

    const VERSION_URL = 'https://padraic.github.io/humbug/downloads/humbug.version';

    const PHAR_URL = 'https://padraic.github.io/humbug/downloads/humbug.phar';

    const PACKAGE_NAME = 'humbug/humbug';

    const FILE_NAME = 'humbug.phar';

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        /**
         * This specific code assumes migration from manual SHA-1 tracked
         * development versions to either a pre-release (default) or solely
         * a stable version. An option is allowed for going back to manual
         * phar builds at the development level.
         *
         * Will be updated again once the stable track is established.
         */
        if ($input->getOption('pre')) {
            $this->updateToPreReleaseBuild();
            return;
        }
        /**
         * This is the final development build being installed automatically
         * Any future dev updates need to carry the --dev flag in command.
         */
        $this->updateToDevelopmentBuild();
         // alpha/beta/rc?

        // Can likely add update config at some point...
    }

    protected function updateToStableBuild()
    {
        $updater = new Updater;
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $this->updateUsingGithubReleases($updater);
    }

    protected function updateToPreReleaseBuild()
    {
        $updater = new Updater;
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setStability('unstable');
        $this->updateUsingGithubReleases($updater);
    }

    protected function updateToDevelopmentBuild()
    {
        $updater = new Updater;
        $updater->getStrategy()->setPharUrl(self::PHAR_URL);
        $updater->getStrategy()->setVersionUrl(self::VERSION_URL);
        $this->update($updater);
    }

    protected function updateUsingGithubReleases(Updater $updater)
    {
        $updater->getStrategy()->setPackageName(self::PACKAGE_NAME);
        $updater->getStrategy()->setPharName(self::FILE_NAME);
        $updater->getStrategy()->setCurrentLocalVersion(
            $this->getApplication()->getVersion()
        );
        $this->update($updater);
    }

    protected function update(Updater $updater)
    {
        $this->output->writeln('Updating...'.PHP_EOL);
        try {
            $result = $updater->update();

            $newVersion = $updater->getNewVersion();
            $oldVersion = $updater->getOldVersion();
            if (strlen($newVersion) == 40) {
                $newVersion = 'dev-' . $newVersion;
            }
            if (strlen($oldVersion) == 40) {
                $oldVersion = 'dev-' . $oldVersion;
            }
        
            if ($result) {
                $this->output->writeln('<fg=green>Humbug has been updated.</fg=green>');
                $this->output->writeln(sprintf(
                    '<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.',
                    $newVersion
                ));
                $this->output->writeln(sprintf(
                    '<fg=green>Previous version was:</fg=green> <options=bold>%s</options=bold>.',
                    $oldVersion
                ));
            } else {
                $this->output->writeln('<fg=green>Humbug is currently up to date.</fg=green>');
                $this->output->writeln(sprintf(
                    '<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.',
                    $oldVersion
                ));
            }
        } catch (\Exception $e) {
            $this->output->writeln(sprintf('Error: <fg=yellow>%s</fg=yellow>', $e->getMessage()));
        }
        $this->output->write(PHP_EOL);
        $this->output->writeln('You can also select update stability using --dev, --pre (alpha/beta/rc) or --stable.');
    }

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Update humbug.phar to most recent stable, pre-release or development build.')
            ->addOption(
               'dev',
               'd',
               InputOption::VALUE_NONE,
               'Update to most recent development build of Humbug.'
            )
            ->addOption(
               'pre',
               'p',
               InputOption::VALUE_NONE,
               'Update to most recent pre-release version of Humbug (alpha/beta/rc), if higher than stable versions, tagged on Github.'
            )
            ->addOption(
               'stable',
               's',
               InputOption::VALUE_NONE,
               'Update to most recent stable version.'
            )
            ->addOption(
               'check',
               'c',
               InputOption::VALUE_NONE,
               'Checks what updates are available across all possible stability tracks.'
            )
        ;
    }
}
