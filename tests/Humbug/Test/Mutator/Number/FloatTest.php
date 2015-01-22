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

namespace Humbug\Test\Mutator\Number;

use Humbug\Mutator;

class FloatTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenSwitchingZeroForOne()
    {
        $mutation = new Mutator\Number\Float;
        $tokens = [10 => [T_DNUMBER, 0.0]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_DNUMBER, 1.0]], $tokens);
    }

    public function testReturnsTokenSwitchingOneForZero()
    {
        $mutation = new Mutator\Number\Float;
        $tokens = [10 => [T_DNUMBER, 1.0]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_DNUMBER, 0.0]], $tokens);
    }

    public function testReturnsTokenSwitchingBetweenOneAndTwoWithIncrement()
    {
        $mutation = new Mutator\Number\Float;
        $tokens = [10 => [T_DNUMBER, 1.5]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_DNUMBER, 2.5]], $tokens);
    }

    public function testReturnsTokenSwitchingAnyIntegerGreaterThanTwoWithOne()
    {
        $mutation = new Mutator\Number\Float;
        $tokens = [10 => [T_DNUMBER, 2.0]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_DNUMBER, 1.0]], $tokens);
    }

    public function testMutatesFloat()
    {
        $tokens = [10 => [T_DNUMBER, 1.05]];
        $this->assertTrue(Mutator\Number\Float::mutates($tokens, 10));
    }

    public function testDoesNotMutateOtherScalars()
    {
        $tokens = [11 => [T_STRING, 'foo']];
        $this->assertFalse(Mutator\Number\Float::mutates($tokens, 11));

        $tokens = [11 => [T_LNUMBER, 0]];
        $this->assertFalse(Mutator\Number\Float::mutates($tokens, 11));

        $tokens = [11 => [T_LNUMBER, 1]];
        $this->assertFalse(Mutator\Number\Float::mutates($tokens, 11));
    }
}
