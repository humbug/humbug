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

class LogicalOrTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToBooleanAnd()
    {
        $mutation = new Mutator\Boolean\LogicalOr;
        $this->assertEquals(
            array(
                10 => array(T_BOOLEAN_AND, '&&')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

    public function testMutatesLogicalOrToLogicalAnd()
    {
        $tokens = array(10 => array(T_BOOLEAN_OR, '||'));

        $this->assertTrue(Mutator\Boolean\LogicalOr::mutates($tokens, 10));

        $tokens = array(11 => array(T_BOOLEAN_AND, '&&'));

        $this->assertFalse(Mutator\Boolean\LogicalOr::mutates($tokens, 11));
    }
}
