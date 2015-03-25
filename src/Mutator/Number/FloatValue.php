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

class FloatValue extends MutatorAbstract
{

    /**
     * Replace 0.0 with 1.0, 1.0 with 0.0, and float between 1 and 2 is incremented
     * by one, and any float greater than 2 is replaced with 1.0.
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array &$tokens, $index)
    {
        $num = (float) $tokens[$index][1];
        if ($num == 0) {
            $replace = 1.0;
        } elseif ($num == 1) {
            $replace = 0.0;
        } elseif ($num < 2) {
            $replace = $num + 1;
        } else {
            $replace = 1.0;
        }
        $tokens[$index] = [
            T_DNUMBER,
            sprintf("%.2f", $replace)
        ];
    }

    public static function mutates(array &$tokens, $index)
    {
        $t = $tokens[$index];
        if (is_array($t) && $t[0] == T_DNUMBER) {
            return true;
        }
        return false;
    }
}
