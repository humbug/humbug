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

use Humbug\Mutator\Number\IntegerValue;

class IntegerValueTest extends \PHPUnit\Framework\TestCase
{
    public function testReturnsTokenSwitchingZeroForOne()
    {
        $mutation = new IntegerValue;
        $tokens = [10 => [T_LNUMBER, 0]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_LNUMBER, 1]], $tokens);
    }

    public function testReturnsTokenSwitchingOneForZero()
    {
        $mutation = new IntegerValue;
        $tokens = [10 => [T_LNUMBER, 1]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_LNUMBER, 0]], $tokens);
    }

    public function testReturnsTokenSwitchingAnyIntegerGreaterThanOneWithItsIncrement()
    {
        $mutation = new IntegerValue;
        $tokens = [10 => [T_LNUMBER, 2]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_LNUMBER, 3]], $tokens);
    }

    public function testMutatesInteger()
    {
        $tokens = [10 => [T_LNUMBER, 0]];
        $this->assertTrue(IntegerValue::mutates($tokens, 10));
    }

    public function testDoesNotMutateOtherScalars()
    {
        $tokens = [11 => [T_STRING, 'foo']];
        $this->assertFalse(IntegerValue::mutates($tokens, 11));

        $tokens = [11 => [T_DNUMBER, 0.0]];
        $this->assertFalse(IntegerValue::mutates($tokens, 11));

        $tokens = [11 => [T_DNUMBER, 1.0]];
        $this->assertFalse(IntegerValue::mutates($tokens, 11));
    }

    public function testDoesMutateOtherIntegerForms()
    {
        $tokens = [11 => [T_LNUMBER, 0123]];
        $this->assertTrue(IntegerValue::mutates($tokens, 11));

        $tokens = [11 => [T_LNUMBER, 0x1A]];
        $this->assertTrue(IntegerValue::mutates($tokens, 11));

        $tokens = [11 => [T_LNUMBER, 0b11111111]];
        $this->assertTrue(IntegerValue::mutates($tokens, 11));
    }
}
