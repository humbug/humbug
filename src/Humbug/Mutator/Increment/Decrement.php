<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator\Increment;

use Humbug\Mutator\MutatorAbstract;

class Decrement extends MutatorAbstract
{
    /**
     * Replace T_DEC (--) with T_INC (++) 
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array $tokens, $index)
    {
        $tokens[$index][0] = T_INC;
        $tokens[$index][1] = '++';
        return $tokens;
    }

    public static function mutates(array $tokens, $index)
    {
        $t = $tokens[$index];
        if (is_array($t) && $t[0] == T_DEC) {
            return true;
        }
        return false;
    }

}
