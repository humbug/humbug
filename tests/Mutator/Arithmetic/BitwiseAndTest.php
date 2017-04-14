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

namespace Humbug\Test\Mutator\Arithmetic;

use Humbug\Mutator;

class BitwiseAndTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTokenEquivalentToBitwiseOrOperator()
    {
        $mutation = new Mutator\Arithmetic\BitwiseAnd;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => '|'], $tokens);
    }

    public function testMutatesBitwiseAndToBitwiseOr()
    {
        $tokens = [10 => '&'];

        $this->assertTrue(Mutator\Arithmetic\BitwiseAnd::mutates($tokens, 10));

        $tokens = [11 => '|'];

        $this->assertFalse(Mutator\Arithmetic\BitwiseAnd::mutates($tokens, 11));
    }

    public function getByRefContexts()
    {
        return [
            [ [ '=', T_WHITESPACE, '&', T_FUNCTION ], 2 ],
            [ [ '=', T_WHITESPACE, T_WHITESPACE, '&', T_FUNCTION ], 3 ],
            [ [ '=', T_WHITESPACE, '&', T_WHITESPACE, T_FUNCTION ], 2 ],
            [ [ '=', '&', T_WHITESPACE, T_FUNCTION ], 1 ],
            [ [ '=', '&', T_WHITESPACE, T_WHITESPACE, T_FUNCTION ], 1 ],

        ];
    }

    /**
     * @dataProvider getByRefContexts
     */
    public function testDoesNotMutateByRefAssignments(array $tokens, $index)
    {
        $this->assertFalse(Mutator\Arithmetic\BitwiseAnd::mutates($tokens, $index));
    }
}
