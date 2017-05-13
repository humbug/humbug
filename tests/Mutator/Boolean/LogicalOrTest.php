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
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_BOOLEAN_AND, '&&']], $tokens);
    }

    public function testMutatesLogicalOrToLogicalAnd()
    {
        $tokens = [10 => [T_BOOLEAN_OR, '||']];

        $this->assertTrue(Mutator\Boolean\LogicalOr::mutates($tokens, 10));

        $tokens = [11 => [T_BOOLEAN_AND, '&&']];

        $this->assertFalse(Mutator\Boolean\LogicalOr::mutates($tokens, 11));
    }
}
