<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug;

use Humbug\Exception\RunkitFailedException;

class Runkit
{

    /**
     * Method signature hash appended to a replaced method's name so it can
     * be reinstated later without any need to separately store entire method
     * related code blocks.
     *
     * @var string
     */
    protected $methodPreserveCode = '';

    /**
     * Apply a mutation to the relevant file. Needs to have an autoloader registered.
     *
     * @param array $mutation
     */
    public function applyMutation(array $mutation)
    {
        if (!class_exists($mutation['class'], true)) {
            throw new RunkitFailedException(self::class . ' could not locate class: ' . $mutation['class']);
        }
        $newBlock = $mutation['mutation']->mutate($mutation['tokens'], $mutation['index']);
        $newArguments = $mutation['args'];

        /**
         * Expand short class names to full w/namespace for runkit
         */
        if (!empty($mutation['replace'])) {
            foreach ($mutation['replace'] as $replace) {
                if (empty($replace) || empty($replace[0])) continue;
                $newRef = preg_quote($replace[0]);
                $find = [
                    "%([^a-zA-Z0-9\\\\])".$newRef."(\\:\\:)%",
                    "%(new\\s+)".$newRef."(\\s*[\\(\\)\\[\\];,\\:]*)%"
                ];
                $newBlock = preg_replace($find, "\$1".$replace[1]."\$2", $newBlock);
                if (!empty($newArguments)) {
                    $newArguments = preg_replace($find, "\$1".$replace[1]."\$2", $newArguments);
                }
            }
        }
        
        $this->methodPreserveCode = md5($mutation['method']);
        if (runkit_method_rename(
            $mutation['class'],
            $mutation['method'],
            $mutation['method'] . $this->methodPreserveCode
        ) == false) {
            throw new RunkitFailedException(
                'runkit_method_rename() failed from ' . $mutation['class']
                . '::' . $mutation['method'] . ' to ' . $mutation['class']
                . '::' . $mutation['method'] . $this->methodPreserveCode
                . ' (mutation application)'
            );
        }
        if(runkit_method_add(
            $mutation['class'],
            $mutation['method'],
            $newArguments,
            $newBlock,
            $this->getMethodFlags($mutation)
        ) == false) {
            throw new RunkitFailedException(
                'runkit_method_add() failed when replacing original '
                . $mutation['class'] . '::' . $mutation['method']
                . '(' . var_export($newArguments, true) . ') with a mutation of'
                . ' type ' . get_class($mutation['mutation']) . ' using the'
                . ' following (mutated) source code from '
                . $mutation['mutation']->getFilename() . ':' . PHP_EOL
                . $newBlock
            );
        }
    }

    /**
     * Reverse a previously applied mutation to the given file
     *
     * @param array $mutation
     */
    public function reverseMutation(array $mutation)
    {
        if(runkit_method_remove(
            $mutation['class'],
            $mutation['method']
        ) == false) {
            throw new RunkitFailedException(
                'runkit_method_remove() failed attempting to remove '
                . $mutation['class'] . '::' . $mutation['method']
            );
        }
        if(runkit_method_rename(
            $mutation['class'],
            $mutation['method'] . $this->methodPreserveCode,
            $mutation['method']
        ) == false) {
            throw new RunkitFailedException(
                'runkit_method_rename() failed renaming from '
                . $mutation['class'] . '::' . $mutation['method']
                . $this->methodPreserveCode . ' to ' . $mutation['class']
                . '::' . $mutation['method'] . ' (mutation reversal)'
            );
        }
    }

    /**
     * Get the appropriate ext/runkit method flag value to use during
     * a replacement via the runkit methods
     *
     * @param array $mutation
     * @return int
     */
    public function getMethodFlags(array $mutation)
    {
        $reflectionClass = new \ReflectionClass($mutation['class']);
        $reflectionMethod = $reflectionClass->getMethod(
            $mutation['method'] . $this->methodPreserveCode
        );
        $static = null;
        $access = null;
        if ($reflectionMethod->isPublic()) {
            $access = RUNKIT_ACC_PUBLIC;
        } elseif ($reflectionMethod->isProtected()) {
            $access = RUNKIT_ACC_PROTECTED;
        } elseif ($reflectionMethod->isPrivate()) {
            $access = RUNKIT_ACC_PRIVATE;
        }
        if (defined('RUNKIT_ACC_STATIC') && $reflectionMethod->isStatic()) {
            $static = RUNKIT_ACC_STATIC;
        }
        if (!is_null($static)) {
            return $access | $static;
        }
        return $access;
    }
    
}
