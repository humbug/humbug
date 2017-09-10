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

class ShiftRightTest extends \PHPUnit\Framework\TestCase
{
    public function testReturnsTokenEquivalentToShiftLeftOperator()
    {
        $mutation = new Mutator\Arithmetic\ShiftRight;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_SL, '<<']], $tokens);
    }

    public function testMutatesShiftRightToShiftLeft()
    {
        $tokens = [10 => [T_SR, '>>']];

        $this->assertTrue(Mutator\Arithmetic\ShiftRight::mutates($tokens, 10));

        $tokens = [11 => [T_SL, '<<']];

        $this->assertFalse(Mutator\Arithmetic\ShiftRight::mutates($tokens, 11));
    }
}
