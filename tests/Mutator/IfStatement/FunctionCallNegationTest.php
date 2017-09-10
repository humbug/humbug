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

namespace Humbug\Test\Mutator\IfStatement;

use Humbug\Mutator\IfStatement\FunctionCallNegation;
use Humbug\Utility\Tokenizer;

class FunctionCallNegationTest extends \PHPUnit\Framework\TestCase
{
    public function testNotMutatesWithValueTrue()
    {
        $content = '<?php if(true){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FunctionCallNegation::mutates($tokens, 2));
    }

    // Positive Checks

    public function testMutatesWithANormalFunction()
    {
        $content = '<?php if(is_int(1)){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FunctionCallNegation::mutates($tokens, 2));
    }

    public function testMutatesWithFunctionInBrackets()
    {
        $content = '<?php if((is_int(1))){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FunctionCallNegation::mutates($tokens, 3));
    }

    // TODO: Repeat for all allow non-bracket delimits defined in class

    public function testMutatesWithFunctionFollowedByLogicalAnd()
    {
        $content = '<?php if(is_int(1)&&true){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FunctionCallNegation::mutates($tokens, 2));
    }

    public function testMutatesWithFunctionPreceededByLogicalAnd()
    {
        $content = '<?php if(true&&is_int(1)){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(FunctionCallNegation::mutates($tokens, 4));
    }

    // Negative Checks

    public function testNotMutatesWithFunctionPreceededByLogicalNot()
    {
        $content = '<?php if(!is_int(1)){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FunctionCallNegation::mutates($tokens, 3));
    }

    public function testNotMutatesWithBracketedFunctionPreceededByLogicalNot()
    {
        $content = '<?php if(!(is_int(1))){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FunctionCallNegation::mutates($tokens, 4));
    }

    public function testNotMutatesWithFunctionFollowedByComparison()
    {
        $content = '<?php if(is_int(1)===TRUE){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FunctionCallNegation::mutates($tokens, 2));
    }

    public function testNotMutatesWithFunctionPreceededByComparison()
    {
        $content = '<?php if(TRUE===is_int(1)){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(FunctionCallNegation::mutates($tokens, 4));
    }

    // Check Mutation

    public function testGetMutationWillNegateANormalFunctionCall()
    {
        $content = '<?php if(is_int(1)){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FunctionCallNegation::getMutation($tokens, 2);
        $this->assertSame('if(!is_int(1)){}', Tokenizer::reconstructFromTokens($tokens));
    }

    public function testGetMutationWillNegateANormalFunctionCallInBrackets()
    {
        $content = '<?php if((is_int(1))){}';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        FunctionCallNegation::getMutation($tokens, 3);
        $this->assertSame('if((!is_int(1))){}', Tokenizer::reconstructFromTokens($tokens));
    }
}
