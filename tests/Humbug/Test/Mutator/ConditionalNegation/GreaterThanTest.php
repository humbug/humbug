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

namespace Humbug\Test\Mutator\ConditionalNegation;

use Humbug\Mutator;

class GreaterThanTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToLessThanOrEqualTo()
    {
        $mutation = new Mutator\ConditionalNegation\GreaterThan;
        $this->assertEquals(
            array(
                10 => array(T_IS_SMALLER_OR_EQUAL, '<=')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesGreaterThanToLessThanOrEqualTo()
    {
        $tokens = array(10 => '>');

        $this->assertTrue(Mutator\ConditionalNegation\GreaterThan::mutates($tokens, 10));

        $tokens = array(11 => array(T_IS_SMALLER_OR_EQUAL, '<='));

        $this->assertFalse(Mutator\ConditionalNegation\GreaterThan::mutates($tokens, 11));
    }

}
