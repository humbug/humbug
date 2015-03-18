<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Mutator\IfStatement;

use Humbug\Mutator\MutatorAbstract;

class FunctionCallNegation extends MutatorAbstract
{

    /**
     * Where the return value of a non-negated function call is directly
     * evaluated to TRUE or FALSE in an IF statement, reverse the boolean check.
     *
     * This ignores functions subject to operations/comparators (e.g. foo() == 1)
     * which runs the risk of generating equivalent mutants as other mutators. For
     * example foo() == 1 would become foo() != 1 via a ConditionalNegation mutator
     * whereas we could switch if(foo()){} to if(!foo()){}. We also don't mutate
     * original negations since those are flipped by the Boolean\LogicalNot mutator
     * already.
     *
     * @param array $tokens
     * @param int $index
     * @return array
     */
    public static function getMutation(array &$tokens, $index)
    {
        array_splice($tokens, $index, 0, ['!']);
    }

    public static function mutates(array &$tokens, $index)
    {
        $t = $tokens[$index];
        if (is_array($t) && $t[0] == T_STRING && function_exists($t[1])
        && self::isANonNegatedFunctionInIfStatement($tokens, $index)) {
            return true;
        }
        return false;
    }

    protected static function isANonNegatedFunctionInIfStatement(array &$tokens, $index)
    {
        $max = count($tokens) - $index + 1;
        $bracketCount = 0;
        $inArg = false;
        for ($i=$index+1; $i < $max; $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] == T_WHITESPACE) {
                continue;
            } elseif (!is_array($tokens[$i]) && $tokens[$i] == '(') {
                $bracketCount++;
                if (!$inArg) {
                    $inArg = true;
                }
            } elseif (!is_array($tokens[$i]) && $tokens[$i] == ')') {
                $bracketCount--;
            }
            if ($inArg && $bracketCount == 0) {
                break; // exited argument list
            }
        }

        $next = self::getNextToken($tokens, $i, [T_WHITESPACE, ')']);
        $previous = self::getPreviousToken($tokens, $index, [T_WHITESPACE, '(']);
        $statementDelimitersIgnoringBrackets = [
            T_IF, T_ELSE, T_ELSEIF, '{', T_BOOLEAN_OR, T_BOOLEAN_AND, T_LOGICAL_OR,
            T_LOGICAL_AND
        ];
        
        if (in_array($previous, $statementDelimitersIgnoringBrackets)
        && in_array($next, $statementDelimitersIgnoringBrackets)) {
            return true;
        }
    }
}
