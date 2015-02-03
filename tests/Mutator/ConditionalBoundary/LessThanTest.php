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

class LessThanTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToLessThanOrEqualTo()
    {
        $mutation = new Mutator\ConditionalBoundary\LessThan;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_IS_SMALLER_OR_EQUAL, '<=']], $tokens);
    }

    public function testMutatesLessThanToLessThanOrEqualTo()
    {
        $tokens = [10 => '<'];

        $this->assertTrue(Mutator\ConditionalBoundary\LessThan::mutates($tokens, 10));

        $tokens = [11 => '<='];

        $this->assertFalse(Mutator\ConditionalBoundary\LessThan::mutates($tokens, 11));
    }
}
