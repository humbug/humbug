<?php

/**
 * Class collecting all mutants and their results.
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Unit\Observers;

use Humbug\TestSuite\Unit\Result;
use Humbug\TestSuite\Unit\Observer;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class ProgressBarObserver implements Observer
{
    private $progressBar;

    private $isDisabled = false;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('no-progress-bar')) {
            $progressBar = new ProgressBar($output);
            $progressBar->setFormat('verbose');
            $progressBar->setBarWidth(58);

            if (!$output->isDecorated()) {
                $progressBar->setRedrawFrequency(60);
            }

            $this->progressBar = $progressBar;
        } else {
            $this->isDisabled = true;
        }
    }

    public function onStartSuite()
    {
        if (!$this->isDisabled) {
            $this->progressBar->start();
        }
    }

    public function onProgress($count)
    {
        if (!$this->isDisabled) {
            $this->progressBar->setProgress($count);
        }
    }

    public function onStopSuite(Result $result)
    {
        if (!$this->isDisabled) {
            $this->progressBar->finish();
        }
    }

    public function isDisabled()
    {
        return $this->isDisabled;
    }
}
