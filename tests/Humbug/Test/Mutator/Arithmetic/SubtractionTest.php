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

class SubtractionTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToAdditionOperator()
    {
        $mutation = new Mutator\Arithmetic\Subtraction;
        $this->assertEquals(
            [
                10 => '+'
            ],
            $mutation->getMutation([], 10)
        );
    }

    public function testMutatesSubtractionOperatorToAdditionOperator()
    {
        $tokens = [10 => '-'];

        $this->assertTrue(Mutator\Arithmetic\Subtraction::mutates($tokens, 10));

        $tokens = [11 => '+'];

        $this->assertFalse(Mutator\Arithmetic\Subtraction::mutates($tokens, 11));
    }

}
