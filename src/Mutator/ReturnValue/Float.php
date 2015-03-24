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

class Float extends MutatorAbstract
{

    /**
     * This is covered by the Number\Float mutator and currently disabled pending
     * some checks on whether a returned literal float should be handled any
     * differently.
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
            } elseif (is_array($tokens[$i]) && $tokens[$i][0] == T_DNUMBER) {
                $num = (float) $tokens[$i][1];
                $replace = 0.0;
                if ($num == 0) {
                    $replace = 1.0;
                } elseif ($num > 1) {
                    $replace = 0.0;
                }
                $tokens[$i] = [
                    T_DNUMBER,
                    sprintf("%.2f", $replace)
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
                } elseif (is_array($tokens[$i]) && $tokens[$i][0] == T_DNUMBER) {
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
