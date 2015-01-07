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

class EqualTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToNotEqual()
    {
        $mutation = new Mutator\ConditionalNegation\Equal;
        $this->assertEquals(
            [
                10 => [T_IS_NOT_EQUAL, '!=']
            ],
            $mutation->getMutation([], 10)
        );
    }

    public function testMutatesEqualToNotEqual()
    {
        $tokens = [10 => [T_IS_EQUAL, '==']];

        $this->assertTrue(Mutator\ConditionalNegation\Equal::mutates($tokens, 10));

        $tokens = [11 => [T_IS_NOT_EQUAL, '!=']];

        $this->assertFalse(Mutator\ConditionalNegation\Equal::mutates($tokens, 11));
    }

}
