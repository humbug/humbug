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

class BooleanAnd extends MutationAbstract
{
    /**
     * Replace T_BOOLEAN_AND (&&) with T_BOOLEAN_OR (||) 
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public function getMutation(array $tokens, $index)
    {
        $tokens[$index][0] = T_BOOLEAN_OR;
        $tokens[$index][1] = '||';
        return $tokens;
    }

}
