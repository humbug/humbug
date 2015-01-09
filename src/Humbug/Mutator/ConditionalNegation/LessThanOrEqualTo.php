<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator\ConditionalNegation;

use Humbug\Mutator\MutatorAbstract;

class LessThanOrEqualTo extends MutatorAbstract
{

    /**
     * Replace (<=) with (>)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array $tokens, $index)
    {
        $tokens[$index] = '>';
        return $tokens;
    }

    public static function mutates(array $tokens, $index)
    {
        $t = $tokens[$index];
        if (is_array($t) && $t[0] == T_IS_SMALLER_OR_EQUAL) {
            return true;
        }
        return false;
    }

}
