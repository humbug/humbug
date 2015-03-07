<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant;

use Humbug\Adapter\AdapterAbstract;
use Humbug\Exception\RuntimeException;
use Humbug\Mutant;
use Symfony\Component\Process\PhpProcess;

class Process
{
    /**
     * @var AdapterAbstract
     */
    private $adapter;

    /**
     * @var Mutant
     */
    private $mutant;

    /**
     * @var PhpProcess
     */
    private $process;

    /**
     * @var bool
     */
    private $isTimeout = false;

    /**
     * @var bool
     */
    private $resultProcessed = false;

    /**
     * @param AdapterAbstract $adapter
     * @param Mutant $mutant
     * @param PhpProcess $process
     */
    public function __construct(AdapterAbstract $adapter, Mutant $mutant, PhpProcess $process)
    {
        $this->adapter = $adapter;
        $this->mutant = $mutant;
        $this->process = $process;
    }

    /**
     * @return AdapterAbstract
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return Mutant
     */
    public function getMutant()
    {
        return $this->mutant;
    }

    /**
     * @return PhpProcess
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        if ($this->resultProcessed) {
            throw new RuntimeException('Result has already been processed.');
        }

        $status = Result::getStatusCode(
            $this->adapter->ok($this->process->getOutput()),
            $this->process->isSuccessful(),
            $this->isTimeout
        );

        $result = new Result(
            $this->mutant,
            $status,
            $this->process->getOutput(),
            $this->process->getErrorOutput()
        );

        $this->process->clearOutput();

        $this->resultProcessed = true;

        return $result;
    }

    /**
     * Marks the process as timed out;
     */
    public function markTimeout()
    {
        $this->isTimeout = true;
    }
}
