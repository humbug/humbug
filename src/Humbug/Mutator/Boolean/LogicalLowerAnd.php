<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator\Boolean;

use Humbug\Mutator\MutatorAbstract;

class LogicalLowerAnd extends MutatorAbstract
{
    /**
     * Replace (and) with (or)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public function getMutation(array $tokens, $index)
    {
        $tokens[$index] = [
            T_LOGICAL_OR,
            'or'
        ];
        return $tokens;
    }

    public static function mutates(array $tokens, $index)
    {
        $t = $tokens[$index];
        if (is_array($t) && $t[0] == T_LOGICAL_AND) {
            return true;
        }
        return false;
    }

}
