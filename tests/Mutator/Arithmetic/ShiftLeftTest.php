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

class ShiftLeftTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTokenEquivalentToShiftRightOperator()
    {
        $mutation = new Mutator\Arithmetic\ShiftLeft;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_SR, '>>']], $tokens);
    }

    public function testMutatesShiftLeftToShiftRight()
    {
        $tokens = [10 => [T_SL, '<<']];

        $this->assertTrue(Mutator\Arithmetic\ShiftLeft::mutates($tokens, 10));

        $tokens = [11 => [T_SR, '>>']];

        $this->assertFalse(Mutator\Arithmetic\ShiftLeft::mutates($tokens, 11));
    }
}
