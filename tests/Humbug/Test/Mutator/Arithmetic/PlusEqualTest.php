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

class PlusEqualTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToMinusEqualOperator()
    {
        $mutation = new Mutator\Arithmetic\PlusEqual;
        $this->assertEquals(
            array(
                10 => array(T_MINUS_EQUAL, '-=')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesPlusEqualToMinusEqual()
    {
        $tokens = array(10 => array(T_PLUS_EQUAL, '+='));

        $this->assertTrue(Mutator\Arithmetic\PlusEqual::mutates($tokens, 10));

        $tokens = array(11 => array(T_MINUS_EQUAL, '-='));

        $this->assertFalse(Mutator\Arithmetic\PlusEqual::mutates($tokens, 11));
    }

}
