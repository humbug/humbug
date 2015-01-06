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

use Humbug\Utility\Diff;
use Humbug\Utility\Tokenizer;

abstract class MutatorAbstract
{

    /**
     * Array of original source code tokens prior to mutation
     *
     * @var array
     */
    protected $tokensOriginal = [];

    /**
     * Array of source code tokens after a mutation has been applied
     *
     * @var array
     */
    protected $tokensMutated = [];

    /**
     * Perform a mutation against the given original source code tokens for
     * a mutable element
     *
     * @param array $tokens
     * @param int $index
     */
    public function mutate($tokens, $index)
    {
        $this->tokensOriginal = $tokens;
        $this->tokensMutated = $this->getMutation($this->tokensOriginal, $index);
        return Tokenizer::reconstructFromTokens($this->tokensMutated);
    }

    /**
     * Calculate the unified diff between the original source code and its
     * mutated form
     *
     * @return string
     */
    public function getDiff()
    {
        $original = Tokenizer::reconstructFromTokens($this->tokensOriginal);
        $mutated = Tokenizer::reconstructFromTokens($this->tokensMutated);
        $difference = Diff::difference($original, $mutated);
        return $difference;
    }

    /**
     * Get a new mutation as an array of changed tokens
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    abstract public function getMutation(array $tokens, $index);

}
