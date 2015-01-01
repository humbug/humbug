<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutation;

class ConditionGreaterThan extends MutationAbstract
{

    /**
     * Replace (>) with (>=)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public function getMutation(array $tokens, $index)
    {
        $tokens[$index] = [
            T_IS_GREATER_OR_EQUAL,
            '>='
        ];
        return $tokens;
    }

}
