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

class ModEqualTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToMulEqualOperator()
    {
        $mutation = new Mutator\Arithmetic\ModEqual;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_MUL_EQUAL, '*=']], $tokens);
    }

    public function testMutatesModEqualToMulEqual()
    {
        $tokens = [10 => [T_MOD_EQUAL, '%=']];

        $this->assertTrue(Mutator\Arithmetic\ModEqual::mutates($tokens, 10));

        $tokens = [11 => [T_MUL_EQUAL, '*=']];

        $this->assertFalse(Mutator\Arithmetic\ModEqual::mutates($tokens, 11));
    }
}
