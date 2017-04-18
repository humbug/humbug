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

class BitwiseOrTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTokenEquivalentToBitwiseOrOperator()
    {
        $mutation = new Mutator\Arithmetic\BitwiseOr;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => '&'], $tokens);
    }

    public function testMutatesBitwiseOrToBitwiseAnd()
    {
        $tokens = [10 => '|'];

        $this->assertTrue(Mutator\Arithmetic\BitwiseOr::mutates($tokens, 10));

        $tokens = [11 => '&'];

        $this->assertFalse(Mutator\Arithmetic\BitwiseOr::mutates($tokens, 11));
    }
}
