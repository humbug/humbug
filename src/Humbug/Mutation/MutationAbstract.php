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

use Humbug\Utility\Diff;

abstract class MutationAbstract
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
     * Name and relative path of the file being mutated
     *
     * @var string
     */
    protected $filename;

    /**
     * Constructor; sets name and relative path of the file being mutated
     *
     * @param string $filename
     */
    public function __construct($filename = '')
    {
        $this->filename = $filename;
    }

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
        return $this->reconstructFromTokens($this->tokensMutated);
    }

    /**
     * Return the file path of the file which is currently being assessed for
     * mutations.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Calculate the unified diff between the original source code and its
     * mutated form
     *
     * @return string
     */
    public function getDiff()
    {
        $original = $this->reconstructFromTokens($this->tokensOriginal);
        $mutated = $this->reconstructFromTokens($this->tokensMutated);
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

    /**
     * Reconstruct a new mutation into a source code string based on the
     * returned tokens
     *
     * @param array $tokens
     * @return string
     */
    protected function reconstructFromTokens(array $tokens)
    {
        $str = '';
        foreach ($tokens as $token) {
            if (is_string($token)) {
                $str .= $token;
            } else {
                $str .= $token[1];
            }
        }
        return $str;
    }

}
