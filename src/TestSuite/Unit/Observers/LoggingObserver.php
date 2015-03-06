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

use Humbug\Renderer\Text;
use Humbug\TestSuite\Unit\Observer;
use Humbug\TestSuite\Unit\Result;
use Symfony\Component\Console\Output\OutputInterface;

class LoggingObserver implements Observer
{

    private $totalCount = 0;

    private $renderer;

    private $output;

    private $progressObserver;

    public function __construct(Text $renderer, OutputInterface $output, Observer $progressObserver)
    {
        $this->renderer = $renderer;
        $this->output = $output;
        $this->progressObserver = $progressObserver;
    }

    public function onStartSuite()
    {
        $this->renderer->renderPreTestIntroduction();
        $this->output->writeln("");
        $this->progressObserver->onStartSuite();
    }

    public function onProgress($count)
    {
        $this->totalCount++;
        $this->progressObserver->onProgress($count);
    }

    public function onStopSuite(Result $result)
    {
        $this->progressObserver->onStopSuite($result);
        $this->output->write(PHP_EOL . PHP_EOL);

        if (! $result->isSuccess()) {
            $this->renderer->renderInitialRunFail($result);

            return;
        }

        /**
         * Initial test run was a success!
         */
        $this->renderer->renderInitialRunPass($result, $this->totalCount);
    }
}
