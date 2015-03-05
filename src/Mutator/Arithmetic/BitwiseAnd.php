<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator\Arithmetic;

use Humbug\Mutator\MutatorAbstract;

class BitwiseAnd extends MutatorAbstract
{

    /**
     * Replace (&) with (|)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array &$tokens, $index)
    {
        $tokens[$index] = '|';
    }

    public static function mutates(array &$tokens, $index)
    {
        $t = $tokens[$index];

        if (!is_array($t) && $t == '&') {
            /**
             * Exclude likely uses of ampersand for references
             */
            if (self::getNextToken($tokens, $index, [ T_WHITESPACE ]) === T_VARIABLE) {
                return false;
            }
            if (self::getNextToken($tokens, $index, [ T_WHITESPACE ]) === T_FUNCTION) {
                return false;
            }
            if (self::getPreviousToken($tokens, $index, [ T_WHITESPACE ]) === '=') {
                return false;
            }
            return true;
        }
        return false;
    }
}
