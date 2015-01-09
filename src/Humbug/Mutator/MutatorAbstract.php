<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator;

use Humbug\Utility\Tokenizer;

abstract class MutatorAbstract
{
    /**
     * Perform a mutation against the given original source code tokens for
     * a mutable element
     *
     * @param array $tokens
     * @param int $index
     */
    public static function mutate(array $tokens, $index)
    {
        $tokensMutated = static::getMutation($tokens, $index);
        return Tokenizer::reconstructFromTokens($tokensMutated);
    }

}
