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
        $this->assertEquals(
            array(
                10 => array(T_DIV_EQUAL, '/=')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesMulEqualToDivEqual()
    {
        $tokens = array(10 => array(T_MUL_EQUAL, '*='));

        $this->assertTrue(Mutator\Arithmetic\MulEqual::mutates($tokens, 10));

        $tokens = array(11 => array(T_DIV_EQUAL, '/='));

        $this->assertFalse(Mutator\Arithmetic\MulEqual::mutates($tokens, 11));
    }

}
