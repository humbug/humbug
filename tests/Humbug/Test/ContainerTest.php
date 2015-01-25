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

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldHaveAdapterOptionsAfterCreate()
    {
        $input = [
            'options' => 'adapterOpt1 adapterOpt2'
        ];

        $container = new Container($input);

        $this->assertSame(['adapterOpt1', 'adapterOpt2'], $container->getAdapterOptions());
    }

    public function testGetShouldReturnInputOption()
    {
        $input = [
            'options' => 'adapterOpt1 adapterOpt2',
            'test' => 'test-option'
        ];

        $container = new Container($input);

        $this->assertSame('test-option', $container->get('test'));
    }

    public function testGetShouldRiseExceptionForUnknownOption()
    {
        $input = [
            'options' => null
        ];

        $this->setExpectedException('\InvalidArgumentException');

        $container = new Container($input);

        $container->get('invalid-option');
    }
}
