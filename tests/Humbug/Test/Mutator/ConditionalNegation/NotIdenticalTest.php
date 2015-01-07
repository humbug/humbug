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

class NotIdenticalTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenEquivalentToIdentical()
    {
        $mutation = new Mutator\ConditionalNegation\NotIdentical;
        $this->assertEquals(
            [
                10 => [T_IS_IDENTICAL, '===']
            ],
            $mutation->getMutation([], 10)
        );
    }

    public function testMutatesNotIdenticalToIdentical()
    {
        $tokens = [10 => [T_IS_NOT_IDENTICAL, '!==']];

        $this->assertTrue(Mutator\ConditionalNegation\NotIdentical::mutates($tokens, 10));

        $tokens = [11 => [T_IS_IDENTICAL, '===']];

        $this->assertFalse(Mutator\ConditionalNegation\NotIdentical::mutates($tokens, 11));
    }

}
