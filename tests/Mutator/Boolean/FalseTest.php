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

class FalseTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTokenEquivalentToTrue()
    {
        $mutation = new Mutator\Boolean\FalseValue;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_STRING, 'true']], $tokens);
    }

    public function testMutatesFalseToTrue()
    {
        $tokens = [10 => [T_STRING, 'FALSE']];

        $this->assertTrue(Mutator\Boolean\FalseValue::mutates($tokens, 10));

        $tokens = [11 => [T_STRING, 'TRUE']];

        $this->assertFalse(Mutator\Boolean\FalseValue::mutates($tokens, 11));
    }
}
