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

class LogicalLowerOrTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToLogicalLowerAnd()
    {
        $mutation = new Mutator\Boolean\LogicalLowerOr;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_LOGICAL_AND, 'and']], $tokens);
    }

    public function testMutatesLogicalLowerAndToLogicalLowerOr()
    {
        $tokens = [10 => [T_LOGICAL_OR, 'or']];

        $this->assertTrue(Mutator\Boolean\LogicalLowerOr::mutates($tokens, 10));

        $tokens = [11 => [T_LOGICAL_AND, 'and']];

        $this->assertFalse(Mutator\Boolean\LogicalLowerOr::mutates($tokens, 11));
    }
}
