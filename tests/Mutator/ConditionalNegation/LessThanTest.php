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

namespace Humbug\Test\Mutator\ConditionalNegation;

use Humbug\Mutator;

class LessThanTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTokenEquivalentToGreaterThanOrEqualTo()
    {
        $mutation = new Mutator\ConditionalNegation\LessThan;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_IS_GREATER_OR_EQUAL, '>=']], $tokens);
    }

    public function testMutatesLessThanToGreaterThanOrEqualTo()
    {
        $tokens = [10 => '<'];

        $this->assertTrue(Mutator\ConditionalNegation\LessThan::mutates($tokens, 10));

        $tokens = [11 => [T_IS_GREATER_OR_EQUAL, '>=']];

        $this->assertFalse(Mutator\ConditionalNegation\LessThan::mutates($tokens, 11));
    }
}
