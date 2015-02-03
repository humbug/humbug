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

class LogicalNotTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToWhitespace()
    {
        $mutation = new Mutator\Boolean\LogicalNot;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_WHITESPACE, '']], $tokens);
    }

    public function testMutatesLogicalNotToEmptyWhitespace()
    {
        $tokens = [10 => '!'];

        $this->assertTrue(Mutator\Boolean\LogicalNot::mutates($tokens, 10));

        $tokens = [11 => '!!'];

        $this->assertFalse(Mutator\Boolean\LogicalNot::mutates($tokens, 11));
    }
}
