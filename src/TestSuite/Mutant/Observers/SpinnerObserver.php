<?php
/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\TestSuite\Mutant\Observers;

use Humbug\Mutable;
use Humbug\TestSuite\Mutant\BaseObserver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpinnerObserver extends BaseObserver
{

    /**
     * @var array
     */
    private $states = ['\\', '|', '/', '-'];

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var float
     */
    private $time;

    /**
     * @var int
     */
    private $refresh;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param OutputInterface $output
     * @param int $refresh Minimum refresh threshold in milliseconds
     */
    public function __construct(InputInterface $input, OutputInterface $output, $refresh = 100)
    {
        $this->input = $input;
        $this->output = $output;
        $this->refresh = $refresh;
    }

    /**
     * @param Mutable $mutable
     * @return void
     */
    public function onProcessedMutable(Mutable $mutable)
    {
        if ($this->input->getOption('no-progress-bar') || !$this->output->isDecorated()) {
            return;
        }

        $time = microtime(true);
        $interval = $time - $this->time;
        $this->time = $time;
        if (round($interval*1000) > $this->refresh) {
            $state = $this->states[($this->count % count($this->states))];
            $this->count++;
            $this->overwrite($state);
        }
    }

    /**
     * @return void
     */
    public function onMutationsGenerated()
    {
        if ($this->input->getOption('no-progress-bar') || !$this->output->isDecorated()) {
            return;
        }

        $this->moveToLineStart();
    }

    /**
     * @param array $states
     */
    public function setStates(array $states)
    {
        $this->states = $states;
    }

    private function overwrite($state)
    {
        if ($this->count > 0) {
            $this->moveToLineStart();
        }
        $this->output->write($state.' ');
    }

    private function moveToLineStart()
    {
        $this->output->write("\x0D");
    }
}
