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

class MulEqualTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToDivEqualOperator()
    {
        $mutation = new Mutator\Arithmetic\MulEqual;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_DIV_EQUAL, '/=']], $tokens);
    }

    public function testMutatesMulEqualToDivEqual()
    {
        $tokens = [10 => [T_MUL_EQUAL, '*=']];

        $this->assertTrue(Mutator\Arithmetic\MulEqual::mutates($tokens, 10));

        $tokens = [11 => [T_DIV_EQUAL, '/=']];

        $this->assertFalse(Mutator\Arithmetic\MulEqual::mutates($tokens, 11));
    }

}
