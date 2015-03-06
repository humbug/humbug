<?php

namespace Humbug\TestSuite\Unit\Observers;

use Humbug\TestSuite\Unit\Result;
use Humbug\TestSuite\Unit\Observer;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBarObserver implements Observer
{

    private $progressBar;

    public function __construct(OutputInterface $output)
    {
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('verbose');
        $progressBar->setBarWidth(58);

        if (!$output->isDecorated()) {
            $progressBar->setRedrawFrequency(60);
        }

        $this->progressBar = $progressBar;
    }

    public function onStartSuite()
    {
        $this->progressBar->start();
    }

    public function onProgress($count)
    {
        $this->progressBar->setProgress($count);
    }

    public function onStopSuite(Result $result)
    {
        $this->progressBar->finish();
    }

}