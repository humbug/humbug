<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\Test\TestSuite\Mutant;

use Humbug\TestSuite\Mutant\Process;
use Humbug\TestSuite\Mutant\Result;
use Prophecy\Argument;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testPropertiesReturnConstructorAssignedValues()
    {
        $adapter = $this->prophesize('Humbug\Adapter\AdapterAbstract');
        $adapter->ok(Argument::type('string'))->willReturn(true);

        $mutant = $this->prophesize(('Humbug\Mutant'));

        $process = $this->prophesize('Symfony\Component\Process\Process');
        $process->clearOutput()->willReturn(null);
        $process->getExitCode()->willReturn(0);
        $process->getOutput()->willReturn('');
        $process->getErrorOutput()->willReturn('');

        $adapter = $adapter->reveal();
        $mutant = $mutant->reveal();
        $phpProcess = $process->reveal();

        $process = new Process($adapter, $mutant, $phpProcess, 1);

        $this->assertEquals($adapter, $process->getAdapter());
        $this->assertEquals($mutant, $process->getMutant());
        $this->assertEquals($phpProcess, $process->getProcess());
        $this->assertEquals(1, $process->getMutableIndex());
    }

    public function testTimedoutProcessReturnsResultWithTimeoutStatus()
    {
        $adapter = $this->prophesize('Humbug\Adapter\AdapterAbstract');
        $adapter->ok(Argument::type('string'))->willReturn(true);

        $mutant = $this->prophesize(('Humbug\Mutant'));

        $process = $this->prophesize('Symfony\Component\Process\Process');
        $process->clearOutput()->willReturn(null);
        $process->getExitCode()->willReturn(0);
        $process->getOutput()->willReturn('');
        $process->getErrorOutput()->willReturn('');

        $process = new Process($adapter->reveal(), $mutant->reveal(), $process->reveal(), 1);
        $process->markTimeout();

        $result = $process->getResult();

        $this->assertEquals(Result::TIMEOUT, $result->getResult());
    }

    /**
     * @expectedException \Humbug\Exception\RuntimeException
     */
    public function testGetResultCanOnlyBeCalledOnce()
    {
        $adapter = $this->prophesize('Humbug\Adapter\AdapterAbstract');
        $adapter->ok(Argument::type('string'))->willReturn(true);

        $mutant = $this->prophesize(('Humbug\Mutant'));

        $process = $this->prophesize('Symfony\Component\Process\Process');
        $process->clearOutput()->willReturn(null);
        $process->getExitCode()->willReturn(0);
        $process->getOutput()->willReturn('');
        $process->getErrorOutput()->willReturn('');

        $process = new Process($adapter->reveal(), $mutant->reveal(), $process->reveal(), 1);
        $process->markTimeout();

        $process->getResult();
        $process->getResult();
    }
}
