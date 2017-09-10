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

class PlusEqualTest extends \PHPUnit\Framework\TestCase
{
    public function testReturnsTokenEquivalentToMinusEqualOperator()
    {
        $mutation = new Mutator\Arithmetic\PlusEqual;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_MINUS_EQUAL, '-=']], $tokens);
    }

    public function testMutatesPlusEqualToMinusEqual()
    {
        $tokens = [10 => [T_PLUS_EQUAL, '+=']];

        $this->assertTrue(Mutator\Arithmetic\PlusEqual::mutates($tokens, 10));

        $tokens = [11 => [T_MINUS_EQUAL, '-=']];

        $this->assertFalse(Mutator\Arithmetic\PlusEqual::mutates($tokens, 11));
    }
}
