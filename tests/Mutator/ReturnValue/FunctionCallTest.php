<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test\Mutator\FunctionCall;

use Humbug\Mutator;

class FunctionCallTest extends \PHPUnit_Framework_TestCase
{

    public function testMutatesWithValueReturnTrue()
    {
        // return false;
        $tokens = [
            0 => [
                0 => T_RETURN,
                1 => 'return',
            ],
            1 => [
                0 => T_WHITESPACE,
                1 => ' ',
            ],
            2 => [
                0 => 308,
                1 => 'true',
            ],
            3 => ';',
        ];
        $this->assertFalse(Mutator\ReturnValue\FunctionCall::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnAssociative()
    {
        // @todo is this really a false case?
        // return (count($foo));
        $tokens = [
            0 => [
                0 => T_RETURN,
                1 => 'return',
            ],
            1 => [
                0 => T_WHITESPACE,
                1 => ' ',
            ],
            2 => '(',
            3 => [
                0 => T_STRING,
                1 => 'count',
            ],
            4 => '(',
            5 => [
                0 => T_VARIABLE,
                1 => '$foo',
            ],
            6 => ')',
            7 => ')',
            8 => ';',
        ];
        $this->assertFalse(Mutator\ReturnValue\FunctionCall::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnFunction()
    {
        // return count($foo);
        $tokens = [
            0 => [
                0 => T_RETURN,
                1 => 'return',
            ],
            1 => [
                0 => T_WHITESPACE,
                1 => ' ',
            ],
            2 => [
                0 => T_STRING,
                1 => 'count',
            ],
            3 => '(',
            4 => [
                0 => T_VARIABLE,
                1 => '$foo',
            ],
            5 => ')',
            6 => ';',
        ];
        $this->assertTrue(Mutator\ReturnValue\FunctionCall::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnFunctionAndString()
    {
        // return count('foo') . 'bar';
        $tokens = [
            0 => [
                0 => T_RETURN,
                1 => 'return',
            ],
            1 => [
                0 => T_WHITESPACE,
                1 => ' ',
            ],
            2 => [
                0 => T_STRING,
                1 => 'count',
            ],
            3 => '(',
            4 => [
                0 => T_VARIABLE,
                1 => '$foo',
            ],
            5 => ')',
            6 => [
                0 => T_WHITESPACE,
                1 => ' ',
            ],
            7 => '.',
            8 => [
                0 => T_WHITESPACE,
                1 => ' ',
            ],
            9 => [
                0 => T_CONSTANT_ENCAPSED_STRING,
                1 => 'foo',
            ],
            10 => ";",
        ];
        $this->assertTrue(Mutator\ReturnValue\FunctionCall::mutates($tokens, 0));
    }
}
