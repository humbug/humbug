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

use Humbug\Mutator\ReturnValue\FunctionCall;
use Humbug\Utility\Tokenizer;

class FunctionCallTest extends \PHPUnit_Framework_TestCase
{

    public function testNotMutatesWithValueReturnTrue()
    {
        $content = '<?php return true;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FunctionCall::mutates($tokens, 0));
    }

    public function testNotMutatesWithValueReturnTrueForNewObjects()
    {
        $content = '<?php return new Foo();';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FunctionCall::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnFunctionCallNoParams()
    {
        $content = '<?php return rand();';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FunctionCall::mutates($tokens, 0));
    }

    // Abusing rand() but it's a recognised function name, and not linting!
    public function testMutatesWithValueReturnFunctionCallWithParams()
    {
        $content = '<?php return rand(1, "foo", 0.3);';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FunctionCall::mutates($tokens, 0));
    }

    public function testGetsMutationSettingReturnValueNullAndPreservingFunctionCall()
    {
        $content = '<?php return rand(1, "foo", 0.3);';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FunctionCall::getMutation($tokens, 0);
        $this->assertSame('rand(1, "foo", 0.3); return null;', Tokenizer::reconstructFromTokens($tokens));
    }
}
