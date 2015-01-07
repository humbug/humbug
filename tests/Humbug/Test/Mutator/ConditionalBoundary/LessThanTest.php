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

namespace Humbug\Test\Mutator\ConditionalBoundary;

use Humbug\Mutator;

class LessThanTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToLessThanOrEqualTo()
    {
        $mutation = new Mutator\ConditionalBoundary\LessThan;
        $this->assertEquals(
            array(
                10 => array(T_IS_SMALLER_OR_EQUAL, '<=')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesLessThanToLessThanOrEqualTo()
    {
        $tokens = array(10 => '<');

        $this->assertTrue(Mutator\ConditionalBoundary\LessThan::mutates($tokens, 10));

        $tokens = array(11 => '<=');

        $this->assertFalse(Mutator\ConditionalBoundary\LessThan::mutates($tokens, 11));
    }

}
