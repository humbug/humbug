<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\Test;

use Humbug\Mutation;

class MutationTest extends \PHPUnit_Framework_TestCase
{
    public function testMutationProperties()
    {
        $mutation = new Mutation('/path/to/file', 1, 'MyClass', 'method', 2, 'Mutator');

        $this->assertEquals('/path/to/file', $mutation->getFile());
        $this->assertEquals(1, $mutation->getLine());
        $this->assertEquals('MyClass', $mutation->getClass());
        $this->assertEquals('method', $mutation->getMethod());
        $this->assertEquals(2, $mutation->getIndex());
        $this->assertEquals('Mutator', $mutation->getMutator());
    }
}
