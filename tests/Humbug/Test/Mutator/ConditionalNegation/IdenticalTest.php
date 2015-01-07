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

class IdenticalTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToNotIdentical()
    {
        $mutation = new Mutator\ConditionalNegation\Identical;
        $this->assertEquals(
            array(
                10 => array(T_IS_NOT_IDENTICAL, '!==')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesIdenticalToNotIdentical()
    {
        $tokens = array(10 => array(T_IS_IDENTICAL, '==='));

        $this->assertTrue(Mutator\ConditionalNegation\Identical::mutates($tokens, 10));

        $tokens = array(11 => array(T_IS_NOT_IDENTICAL, '!=='));

        $this->assertFalse(Mutator\ConditionalNegation\Identical::mutates($tokens, 11));
    }

}
