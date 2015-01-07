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

namespace Humbug\Test\Mutator\Boolean;

use Humbug\Mutator;

class TrueTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToFalse()
    {
        $mutation = new Mutator\Boolean\True;
        $this->assertEquals(
            array(
                10 => array(
                    T_STRING, 'false'
                )
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesTrueToFalse()
    {
        $tokens = array(10 => array(T_STRING, 'TRUE'));

        $this->assertTrue(Mutator\Boolean\True::mutates($tokens, 10));

        $tokens = array(11 => array(T_STRING, 'FALSE'));

        $this->assertFalse(Mutator\Boolean\True::mutates($tokens, 11));
    }
}
