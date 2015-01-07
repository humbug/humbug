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

namespace Humbug\Test\Mutation;

use Humbug\Mutator;

class OperatorDecrementTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToIncrementOperator()
    {
        $mutation = new Mutator\Increment\Decrement;
        $this->assertEquals(
            array(
                10 => array(T_INC, '++')
            ),
            $mutation->getMutation(array(), 10)
        );
    }

}
