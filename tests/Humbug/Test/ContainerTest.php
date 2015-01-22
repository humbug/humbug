<?php

/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 *
 * @author     rafal.wartalski@gmail.com
 */

namespace Humbug\Test;

use Humbug\Container;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldHaveAdapterOptionsAfterCreate()
    {
        $input = $this->createInputMock();

        $container = new Container($input);

        $this->assertSame(['adapterOpt1', 'adapterOpt2'], $container->getAdapterOptions());
    }

    private function createInputMock()
    {
        $input = $this->getMock('\Symfony\Component\Console\Input\InputInterface');

        $input->expects($this->at(0))->method('getOption')->with('options')->willReturn('adapterOpt1 adapterOpt2');

        return $input;
    }

    public function testGetShouldReturnInputOption()
    {
        $input = $this->createInputMock();

        $input->expects($this->at(1))->method('getOption')->with('test')->willReturn('test-option');

        $container = new Container($input);

        $this->assertSame('test-option', $container->get('test'));
    }

    public function testGetShouldRiseExceptionForUnknownOption()
    {
        $input = new ArgvInput([], new InputDefinition([new InputOption('options')]));

        $this->setExpectedException('\InvalidArgumentException');

        $container = new Container($input);

        $container->get('invalid-option');
    }

}
