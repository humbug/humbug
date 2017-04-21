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

namespace Humbug\Test\Mutator\Increment;

use Humbug\Mutator;

class DecrementTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTokenEquivalentToIncrementOperator()
    {
        $mutation = new Mutator\Increment\Decrement;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_INC, '++']], $tokens);
    }

    public function testMutatesDecrementToIncrement()
    {
        $tokens = [10 => [T_DEC, '--']];

        $this->assertTrue(Mutator\Increment\Decrement::mutates($tokens, 10));

        $tokens = [11 => [T_INC, '++']];

        $this->assertFalse(Mutator\Increment\Decrement::mutates($tokens, 11));
    }
}
