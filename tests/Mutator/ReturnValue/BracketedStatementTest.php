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

use Humbug\Mutator;
use Humbug\Utility\Tokenizer;

class BracketedStatementTest extends \PHPUnit\Framework\TestCase
{
    public function testDoesNotMutateWithValueReturnTrue()
    {
        // return false;
        $tokens = [
            0 => [
                0 => T_RETURN,
                1 => 'return',
            ],
            1 => [
                0 => T_WHITESPACE,
                1 => ' ',
            ],
            2 => [
                0 => 308,
                1 => 'true',
            ],
            3 => ';',
        ];
        $this->assertFalse(Mutator\ReturnValue\BracketedStatement::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnInBrackets()
    {
        $tokens = [
            0 => [
                0 => T_RETURN,
                1 => 'return',
            ],
            1 => [
                0 => T_WHITESPACE,
                1 => ' ',
            ],
            2 => '(',
            3 => [
                0 => T_STRING,
                1 => 'count',
            ],
            4 => '(',
            5 => [
                0 => T_VARIABLE,
                1 => '$foo',
            ],
            6 => ')',
            7 => ')',
            8 => ';',
        ];
        $this->assertTrue(Mutator\ReturnValue\BracketedStatement::mutates($tokens, 0));
    }

    public function testGetMutationWithValueReturnOneAndPlusDecimal()
    {
        $content = '<?php return (count([1,2]));';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        Mutator\ReturnValue\BracketedStatement::getMutation($tokens, 0);
        $this->assertSame('(count([1,2])); return null;', Tokenizer::reconstructFromTokens($tokens));
    }
}
