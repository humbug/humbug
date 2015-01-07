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

namespace Humbug\Test\Mutator\Increment;

use Humbug\Mutator;

class DecrementTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToIncrementOperator()
    {
        $mutation = new Mutator\Increment\Decrement;
        $this->assertEquals(
            array(
                10 => array(T_INC, '++')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesDecrementToIncrement()
    {
        $tokens = array(10 => array(T_DEC, '--'));

        $this->assertTrue(Mutator\Increment\Decrement::mutates($tokens, 10));

        $tokens = array(11 => array(T_INC, '++'));

        $this->assertFalse(Mutator\Increment\Decrement::mutates($tokens, 11));
    }
}
