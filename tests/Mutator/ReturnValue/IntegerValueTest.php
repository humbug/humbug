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

use Humbug\Mutator\ReturnValue\IntegerValue;
use Humbug\Utility\Tokenizer;

class IntegerValueTest extends \PHPUnit_Framework_TestCase
{
    public function testMutatesWithValueReturnOne()
    {
        $content = '<?php return 1;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(IntegerValue::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnZero()
    {
        $content = '<?php return 1;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(IntegerValue::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnOneAndWhitespaces()
    {
        $content = '<?php return 1   ;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(IntegerValue::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnOneAndPlusDecimal()
    {
        $content = '<?php return 1+1;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(IntegerValue::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnFloat()
    {
        $content = '<?php return 1.0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(IntegerValue::mutates($tokens, 0));
    }

    public function testGetMutationWithValueReturnOne()
    {
        $content = '<?php return 1;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        IntegerValue::getMutation($tokens, 0);
        $this->assertSame($tokens[2][1], "0");
    }

    public function testGetMutationWithValueReturnZero()
    {
        $content = '<?php return 0;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        IntegerValue::getMutation($tokens, 0);
        $this->assertSame($tokens[2][1], "1");
    }

    public function testGetMutationWithValueReturnOneAndPlusDecimal()
    {
        $content = '<?php return 1+1;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        IntegerValue::getMutation($tokens, 0);
        $this->assertSame($tokens[2][1], "0");
    }
}
