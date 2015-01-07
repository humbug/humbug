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

class NotEqualTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToEqual()
    {
        $mutation = new Mutator\ConditionalNegation\NotEqual;
        $this->assertEquals(
            array(
                10 => array(T_IS_EQUAL, '==')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesNotEqualToEqual()
    {
        $tokens = array(10 => array(T_IS_NOT_EQUAL, '!='));

        $this->assertTrue(Mutator\ConditionalNegation\NotEqual::mutates($tokens, 10));

        $tokens = array(11 => array(T_IS_EQUAL, '=='));

        $this->assertFalse(Mutator\ConditionalNegation\NotEqual::mutates($tokens, 11));
    }

}
