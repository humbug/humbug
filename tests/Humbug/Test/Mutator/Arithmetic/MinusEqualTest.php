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

class MinusEqualTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToPlusEqualOperator()
    {
        $mutation = new Mutator\Arithmetic\MinusEqual;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_PLUS_EQUAL, '+=']], $tokens);
    }

    public function testMutatesMinusEqualToPlusEqual()
    {
        $tokens = [10 => [T_MINUS_EQUAL, '-=']];

        $this->assertTrue(Mutator\Arithmetic\MinusEqual::mutates($tokens, 10));

        $tokens = [11 => [T_PLUS_EQUAL, '+=']];

        $this->assertFalse(Mutator\Arithmetic\MinusEqual::mutates($tokens, 11));
    }

}
