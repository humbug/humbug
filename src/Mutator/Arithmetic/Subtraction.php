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

class Subtraction extends MutatorAbstract
{

    /**
     * Replace minus sign (-) with plus sign (+)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array &$tokens, $index)
    {
        $tokens[$index] = '+';
    }

    public static function mutates(array &$tokens, $index)
    {
        $t = $tokens[$index];
        if (!is_array($t) && $t == '-') {
            return true;
        }
        return false;
    }
}
