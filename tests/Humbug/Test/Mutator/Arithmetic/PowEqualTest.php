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

class PowEqualTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToDivEqualOperator()
    {
        $mutation = new Mutator\Arithmetic\PowEqual;
        $this->assertEquals(
            array(
                10 => array(T_DIV_EQUAL, '/=')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesMulEqualToDivEqual()
    {
        if (!defined('T_POW')) {
            $this->markTestSkipped();
        }

        $tokens = array(10 => array(T_POW_EQUAL, '**='));

        $this->assertTrue(Mutator\Arithmetic\PowEqual::mutates($tokens, 10));

        $tokens = array(11 => array(T_DIV_EQUAL, '/='));

        $this->assertFalse(Mutator\Arithmetic\PowEqual::mutates($tokens, 11));
    }

}
