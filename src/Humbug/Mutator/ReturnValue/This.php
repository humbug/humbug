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

class This extends MutatorAbstract
{

    /**
     * Replace (return $this;) with (return null;)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array $tokens, $index)
    {
        for ($i=$index+1; $i < count($tokens); $i++) { 
            if (is_array($tokens[$i]) && $tokens[$i][0] == T_WHITESPACE) {
                continue;
            } elseif (is_array($tokens[$i]) && $tokens[$i][0] == T_VARIABLE && $tokens[$i][1] == '$this') {
                $tokens[$i] = [
                    T_STRING,
                    'null'
                ];
                break;
            }
        }
        return $tokens;
    }

    public static function mutates(array $tokens, $index)
    {
        $t = $tokens[$index];
        if (is_array($t) && $t[0] == T_RETURN) {
            $hasThis = false;
            // effectively, we look for 'return $this;'. Anything else in there and we get out.
            for ($i=$index+1; $i < count($tokens); $i++) { 
                if (is_array($tokens[$i]) && $tokens[$i][0] == T_WHITESPACE) {
                    continue;
                } elseif (is_array($tokens[$i]) && $tokens[$i][0] == T_VARIABLE && $tokens[$i][1] == '$this') {
                    $hasThis = true;
                    continue;
                } elseif (!is_array($tokens[$i]) && $tokens[$i] == ';') {
                    // return statement terminated
                    if ($hasThis === true) {
                        return true;
                    }
                    return false;
                } else {
                    $hasThis = false;
                }
            }
        }
        return false;
    }

}
