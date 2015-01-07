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
        $this->assertEquals(
            array(
                10 => array(T_MUL_EQUAL, '*=')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesModEqualToMulEqual()
    {
        $tokens = array(10 => array(T_MOD_EQUAL, '%='));

        $this->assertTrue(Mutator\Arithmetic\ModEqual::mutates($tokens, 10));

        $tokens = array(11 => array(T_MUL_EQUAL, '*='));

        $this->assertFalse(Mutator\Arithmetic\ModEqual::mutates($tokens, 11));
    }

}
