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

class ShiftRightTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToShiftLeftOperator()
    {
        $mutation = new Mutator\Arithmetic\ShiftRight;
        $this->assertEquals(
            array(
                10 => array(T_SL, '<<')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesShiftRightToShiftLeft()
    {
        $tokens = array(10 => array(T_SR, '>>'));

        $this->assertTrue(Mutator\Arithmetic\ShiftRight::mutates($tokens, 10));

        $tokens = array(11 => array(T_SL, '<<'));

        $this->assertFalse(Mutator\Arithmetic\ShiftRight::mutates($tokens, 11));
    }

}
