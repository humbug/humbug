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
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_BOOLEAN_OR, '||']], $tokens);
    }

    public function testMutatesLogicalAndToLogicalOr()
    {
        $tokens = [10 => [T_BOOLEAN_AND, '&&']];

        $this->assertTrue(Mutator\Boolean\LogicalAnd::mutates($tokens, 10));

        $tokens = [11 => [T_BOOLEAN_OR, '||']];

        $this->assertFalse(Mutator\Boolean\LogicalAnd::mutates($tokens, 11));
    }
}
