<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator;

use Humbug\Utility\Tokenizer;

abstract class MutatorAbstract
{
    /**
     * Perform a mutation against the given original source code tokens for
     * a mutable element
     *
     * @param array $tokens
     * @param int $index
     * @return string
     */
    public static function mutate(array &$tokens, $index)
    {
        static::getMutation($tokens, $index);
        return Tokenizer::reconstructFromTokens($tokens);
    }

    private static function shouldSkip($token, array $excludeTokens)
    {
        if (! is_array($token) && in_array($token, $excludeTokens, true)) {
            return true;
        }

        return in_array($token[0], $excludeTokens, true);
    }

    /**
     * Finds the next token in token array after a given index.
     * @param array $tokens Token array to lookup
     * @param int $index Position to start lookup at
     * @param array $excludeTokens Excluded tokens list
     *
     * @return int|string|false The next match if found, or false. Token is guaranteed to be a scalar if a match is found.
     */
    protected static function getNextToken(array &$tokens, $index, array $excludeTokens = [])
    {
        $tokenCount = count($tokens);
        while ($index < $tokenCount && isset($tokens[$index+1])
        && self::shouldSkip($tokens[$index + 1], $excludeTokens)) {
            $index++;
        }

        if (! isset($tokens[$index+1])) {
            return false;
        }

        return is_array($tokens[$index+1]) ? $tokens[$index+1][0] : $tokens[$index+1];
    }


    /**
     * Finds the next token value in token array before a given index.
     * @param array $tokens Token array to lookup
     * @param int $index Position to start lookup at
     * @param array $excludeTokens Excluded tokens list
     *
     * @return int|string|false The previous match if found, or false. Token is guaranteed to be a scalar if a match is found.
     */
    protected static function getPreviousToken(array &$tokens, $index, array $excludeTokens = [])
    {
        while ($index > 0 && isset($tokens[$index-1])
        && self::shouldSkip($tokens[$index - 1], $excludeTokens)) {
            $index--;
        }

        if (! isset($tokens[$index-1])) {
            return false;
        }

        return is_array($tokens[$index-1]) ? $tokens[$index-1][0] : $tokens[$index-1];
    }
}
