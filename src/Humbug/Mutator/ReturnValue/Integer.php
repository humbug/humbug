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
     * Replace (return $this;) with (return null;)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array &$tokens, $index)
    {
        for ($i=$index+1; $i < count($tokens); $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] == T_WHITESPACE) {
                continue;
            } elseif (is_array($tokens[$i]) && $tokens[$i][0] == T_LNUMBER) {
                $num = (integer) $tokens[$i][1];
                $replace = null;
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
            for ($i=$index+1; $i < count($tokens); $i++) {
                if (is_array($tokens[$i]) && $tokens[$i][0] == T_WHITESPACE) {
                    continue;
                } elseif (is_array($tokens[$i]) && $tokens[$i][0] == T_LNUMBER) {
                    $has = true;
                    continue;
                } elseif (!is_array($tokens[$i]) && $tokens[$i] == ';') {
                    // return statement terminated
                    if ($has === true) {
                        return true;
                    }
                    return false;
                } else {
                    $has = false;
                }
            }
        }
        return false;
    }
}
