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

use Humbug\Mutator\ReturnValue\FloatValue;
use Humbug\Utility\Tokenizer;

class FloatValueTest extends \PHPUnit_Framework_TestCase
{
    public function testMutatesWithValueReturnOne()
    {
        $content = '<?php return 1.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FloatValue::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnZero()
    {
        $content = '<?php return 0.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FloatValue::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnOneAndWhitespaces()
    {
        $content = '<?php return 1.0   ;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FloatValue::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnOneAndPlusDecimal()
    {
        $content = '<?php return 1.0+1.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FloatValue::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnInteger()
    {
        $content = '<?php return 1;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FloatValue::mutates($tokens, 0));
    }

    public function testGetMutationWithValueReturnOne()
    {
        $content = '<?php return 1.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FloatValue::getMutation($tokens, 0);
        $this->assertSame($tokens[2][1], "0.00");
    }

    public function testGetMutationWithValueReturnZero()
    {
        $content = '<?php return 0.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FloatValue::getMutation($tokens, 0);
        $this->assertSame($tokens[2][1], "1.00");
    }

    public function testGetMutationWithValueReturnOneAndPlusDecimal()
    {
        $content = '<?php return 1.0+1.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FloatValue::getMutation($tokens, 0);
        $this->assertSame($tokens[2][1], "0.00");
    }
}
