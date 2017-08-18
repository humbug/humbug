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

class Addition extends MutatorAbstract
{

    /**
     * Replace plus sign (+) with minus sign (-)
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array &$tokens, $index)
    {
        $tokens[$index] = '-';
    }

    /**
     * Not all additions can be mutated.
     *
     * The PHP language allows union of arrays : $var = ['foo' => true] + ['bar' => true]
     * see http://php.net/manual/en/language.operators.array.php for details.
     *
     * So for this case, we can't create a mutation.
     *
     * @param array $tokens
     * @param $index
     * @return bool
     */
    public static function mutates(array &$tokens, $index)
    {
        $t = $tokens[$index];
        if (!is_array($t) && $t == '+') {
            $tokenCount = count($tokens);
            for ($i = $index + 1; $i < $tokenCount; $i++) {
                // check for short array syntax
                if (!is_array($tokens[$i]) && $tokens[$i][0] == '[') {
                    return false;
                }

                // check for long array syntax
                if (is_array($tokens[$i]) && $tokens[$i][0] == T_ARRAY && $tokens[$i][1] == 'array') {
                    return false;
                }

                // if we're at the end of the array
                // and we didn't see any array, we
                // can probably mutate this addition
                if (!is_array($tokens[$i]) && $tokens[$i] == ';') {
                    return true;
                }
            }
            return true;
        }

        return false;
    }
}
