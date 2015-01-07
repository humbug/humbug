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

class ExponentiationTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToDivisionOperator()
    {
        $mutation = new Mutator\Arithmetic\Exponentiation;
        $this->assertEquals(
            array(
                10 => '/'
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesExponentiationToDivision()
    {
        if (!defined('T_POW')) {
            $this->markTestSkipped();
        }

        $tokens = array(10 => array(T_POW));

        $this->assertTrue(Mutator\Arithmetic\Exponentiation::mutates($tokens, 10));

        $tokens = array(11 => '/');

        $this->assertFalse(Mutator\Arithmetic\Exponentiation::mutates($tokens, 11));
    }

}
