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

namespace Humbug\Test\Mutator\ReturnValue;

use Humbug\Mutator\ReturnValue\FloatNegation;
use Humbug\Utility\Tokenizer;

class FloatNegationTest extends \PHPUnit_Framework_TestCase
{
    public function testNotMutatesWithValueReturnTrue()
    {
        $content = '<?php return true;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FloatNegation::mutates($tokens, 0));
    }

    public function testNotMutatesWithValueZero()
    {
        $content = '<?php return 0.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FloatNegation::mutates($tokens, 0));
    }

    public function testNotMutatesWithValueInteger()
    {
        $content = '<?php return 1;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FloatNegation::mutates($tokens, 0));
    }

    public function testMutatesWithValueOne()
    {
        $content = '<?php return 1.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FloatNegation::mutates($tokens, 0));
    }

    public function testMutatesWithValueMinusOneHundred()
    {
        $content = '<?php return -100.35;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FloatNegation::mutates($tokens, 0));
    }

    public function testGetsMutationReversesIntegerSignWhenPositive()
    {
        $content = '<?php return 1.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FloatNegation::getMutation($tokens, 0);
        $this->assertSame('return -1.00;', Tokenizer::reconstructFromTokens($tokens));
    }

    public function testGetsMutationReversesIntegerSignWhenNegative()
    {
        $content = '<?php return -1.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FloatNegation::getMutation($tokens, 0);
        $this->assertSame('return 1.0;', Tokenizer::reconstructFromTokens($tokens));
    }

    public function testGetsMutationReversesIntegerSignWhenNegativeDecimal()
    {
        $content = '<?php return -1.45;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FloatNegation::getMutation($tokens, 0);
        $this->assertSame('return 1.45;', Tokenizer::reconstructFromTokens($tokens));
    }
}
