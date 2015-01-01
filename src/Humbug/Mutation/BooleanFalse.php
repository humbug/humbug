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

class BooleanFalse extends MutationAbstract
{

    /**
     * Replace boolean FALSE with TRUE
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public function getMutation(array $tokens, $index)
    {
        $tokens[$index][0] = T_STRING;
        $tokens[$index][1] = 'true';
        return $tokens;
    }

    public static function mutates(array $tokens, $index)
    {
        $t = $tokens[$index];
        if (!is_array($t) && $t[0] == T_STRING && strtolower($t[1]) == 'false') {
            return true;
        }
        return false;
    }

}
