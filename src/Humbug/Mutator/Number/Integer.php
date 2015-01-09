<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator\Number;

use Humbug\Mutator\MutatorAbstract;

class Integer extends MutatorAbstract
{

    /**
     * Replace 1 with 0, 0 with 1, or increment.
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array $tokens, $index)
    {
        $num = (integer) $tokens[$index][1];
        $replace = null;
        if ($num == 0) {
            $replace = 1;
        } elseif ($num == 1) {
            $replace = 0;
        } else {
            $replace = $num + 1;
        }
        $tokens[$index] = [
            T_LNUMBER,
            (string) $replace
        ];
        return $tokens;
    }

    public static function mutates(array $tokens, $index)
    {
        $t = $tokens[$index];
        if (is_array($t) && $t[0] == T_LNUMBER) {
            return true;
        }
        return false;
    }

}
