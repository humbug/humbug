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

class DivisionTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToMultiplicationOperator()
    {
        $mutation = new Mutator\Arithmetic\Division;
        $this->assertEquals(
            array(
                10 => '*'
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesDivisionToMultiplication()
    {
        $tokens = array(10 => '/');

        $this->assertTrue(Mutator\Arithmetic\Division::mutates($tokens, 10));

        $tokens = array(11 => '*');

        $this->assertFalse(Mutator\Arithmetic\Division::mutates($tokens, 11));
    }

}
