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

class NotTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToWhitespace()
    {
        $mutation = new Mutator\Arithmetic\Not;
        $this->assertEquals(
            array(
                10 => array(T_WHITESPACE, '')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesNotToWhitespace()
    {
        $tokens = array(10 => '~');

        $this->assertTrue(Mutator\Arithmetic\Not::mutates($tokens, 10));
    }

}
