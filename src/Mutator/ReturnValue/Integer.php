<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator\ReturnValue;

use Humbug\Mutator\MutatorAbstract;

class Integer extends MutatorAbstract
{

    /**
     * Replace 0 with 1, 1 with 0 or otherwise 0.
     * Intent being to cover any generic uses of positive values being equivalent
     * to boolean TRUE or FALSE.
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array &$tokens, $index)
    {
        $tokenCount = count($tokens);
        for ($i=$index+1; $i < $tokenCount; $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] == T_WHITESPACE) {
                continue;
            } elseif (is_array($tokens[$i]) && $tokens[$i][0] == T_LNUMBER) {
                $num = (integer) $tokens[$i][1];
                if ($num == 0) {
                    $replace = 1;
                } else {
                    $replace = 0;
                }
                $tokens[$i] = [
                    T_LNUMBER,
                    (string) $replace
                ];
                break;
            }
        }
    }

    public static function mutates(array &$tokens, $index)
    {
        $t = $tokens[$index];
        if (is_array($t) && $t[0] == T_RETURN) {
            $has = false;
            $tokenCount = count($tokens);
            for ($i=$index+1; $i < $tokenCount; $i++) {
                if (is_array($tokens[$i]) && $tokens[$i][0] == T_WHITESPACE) {
                    continue;
                } elseif (is_array($tokens[$i]) && $tokens[$i][0] == T_LNUMBER) {
                    $has = true;
                    continue;
                } elseif (!is_array($tokens[$i]) && $tokens[$i] == ';') {
                    return $has;
                } else {
                    $has = false;
                }
            }
        }
        return false;
    }
}
