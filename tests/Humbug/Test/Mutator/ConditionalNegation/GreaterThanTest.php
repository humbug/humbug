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

class GreaterThanTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToLessThanOrEqualTo()
    {
        $mutation = new Mutator\ConditionalNegation\GreaterThan;
        $tokens = [];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_IS_SMALLER_OR_EQUAL, '<=']], $tokens);
    }

    public function testMutatesGreaterThanToLessThanOrEqualTo()
    {
        $tokens = [10 => '>'];

        $this->assertTrue(Mutator\ConditionalNegation\GreaterThan::mutates($tokens, 10));

        $tokens = [11 => [T_IS_SMALLER_OR_EQUAL, '<=']];

        $this->assertFalse(Mutator\ConditionalNegation\GreaterThan::mutates($tokens, 11));
    }

}
