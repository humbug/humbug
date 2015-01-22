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

class IntegerTest extends \PHPUnit_Framework_TestCase
{

    public function testReturnsTokenSwitchingZeroForOne()
    {
        $mutation = new Mutator\Number\Integer;
        $tokens = [10 => [T_LNUMBER, 0]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_LNUMBER, 1]], $tokens);
    }

    public function testReturnsTokenSwitchingOneForZero()
    {
        $mutation = new Mutator\Number\Integer;
        $tokens = [10 => [T_LNUMBER, 1]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_LNUMBER, 0]], $tokens);
    }

    public function testReturnsTokenSwitchingAnyIntegerGreaterThanOneWithItsIncrement()
    {
        $mutation = new Mutator\Number\Integer;
        $tokens = [10 => [T_LNUMBER, 2]];
        $mutation->getMutation($tokens, 10);
        $this->assertEquals([10 => [T_LNUMBER, 3]], $tokens);
    }

    public function testMutatesInteger()
    {
        $tokens = [10 => [T_LNUMBER, 0]];
        $this->assertTrue(Mutator\Number\Integer::mutates($tokens, 10));
    }

    public function testDoesNotMutateOtherScalars()
    {
        $tokens = [11 => [T_STRING, 'foo']];
        $this->assertFalse(Mutator\Number\Integer::mutates($tokens, 11));

        $tokens = [11 => [T_DNUMBER, 0.0]];
        $this->assertFalse(Mutator\Number\Integer::mutates($tokens, 11));

        $tokens = [11 => [T_DNUMBER, 1.0]];
        $this->assertFalse(Mutator\Number\Integer::mutates($tokens, 11));
    }
}
