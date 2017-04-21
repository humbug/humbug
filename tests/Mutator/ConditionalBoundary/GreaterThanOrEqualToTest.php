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

namespace Humbug\Test\Mutator\ConditionalBoundary;

use Humbug\Mutator;

class GreaterThanOrEqualToTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTokenEquivalentToGreaterThanOrEqualTo()
    {
        $mutation = new Mutator\ConditionalBoundary\GreaterThanOrEqualTo;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => '>'], $tokens);
    }

    public function testMutatesGreaterThanToGreaterThanOrEqualTo()
    {
        $tokens = [10 => [T_IS_GREATER_OR_EQUAL, '>=']];

        $this->assertTrue(Mutator\ConditionalBoundary\GreaterThanOrEqualTo::mutates($tokens, 10));

        $tokens = [11 => '>'];

        $this->assertFalse(Mutator\ConditionalBoundary\GreaterThanOrEqualTo::mutates($tokens, 11));
    }
}
