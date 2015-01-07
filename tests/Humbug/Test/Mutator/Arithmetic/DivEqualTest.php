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

class DivEqualTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToMulEqualOperator()
    {
        $mutation = new Mutator\Arithmetic\DivEqual;
        $this->assertEquals(
            array(
                10 => array(T_MUL_EQUAL, '*=')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesDivEqualToMulEqual()
    {
        $tokens = array(10 => array(T_DIV_EQUAL, '/='));

        $this->assertTrue(Mutator\Arithmetic\DivEqual::mutates($tokens, 10));

        $tokens = array(11 => array(T_MUL_EQUAL, '*='));

        $this->assertFalse(Mutator\Arithmetic\DivEqual::mutates($tokens, 11));
    }

}
