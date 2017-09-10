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

class DivisionTest extends \PHPUnit\Framework\TestCase
{
    public function testReturnsTokenEquivalentToMultiplicationOperator()
    {
        $mutation = new Mutator\Arithmetic\Division;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => '*'], $tokens);
    }

    public function testMutatesDivisionToMultiplication()
    {
        $tokens = [10 => '/'];

        $this->assertTrue(Mutator\Arithmetic\Division::mutates($tokens, 10));

        $tokens = [11 => '*'];

        $this->assertFalse(Mutator\Arithmetic\Division::mutates($tokens, 11));
    }
}
