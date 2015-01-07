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

class LogicalAndTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToBooleanOr()
    {
        $mutation = new Mutator\Boolean\LogicalAnd;
        $this->assertEquals(
            array(
                10 => array(T_BOOLEAN_OR, '||')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

}
