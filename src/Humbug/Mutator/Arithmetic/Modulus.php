<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator\Arithmetic;

use Humbug\Mutator\MutatorAbstract;

class Modulus extends MutatorAbstract
{

    /**
     * Replace (%) with (*)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public function getMutation(array $tokens, $index)
    {
        $tokens[$index] = '*';
        return $tokens;
    }

    public static function mutates(array $tokens, $index)
    {
        $t = $tokens[$index];
        if (!is_array($t) && $t == '%') {
            return true;
        }
        return false;
    }

}
