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

class GreaterThanOrEqualToTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToLessThan()
    {
        $mutation = new Mutator\ConditionalNegation\GreaterThanOrEqualTo;
        $this->assertEquals(
            array(
                10 => '<'
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesGreaterThanOrEqualToToLessThan()
    {
        $tokens = array(10 => array(T_IS_GREATER_OR_EQUAL, '>='));

        $this->assertTrue(Mutator\ConditionalNegation\GreaterThanOrEqualTo::mutates($tokens, 10));

        $tokens = array(11 => '<');

        $this->assertFalse(Mutator\ConditionalNegation\GreaterThanOrEqualTo::mutates($tokens, 11));
    }

}
