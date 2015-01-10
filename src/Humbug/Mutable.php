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

use Humbug\Utility\Tokenizer;

class Mutable
{

    /**
     * Name and relative path of the file to be mutated
     *
     * @var string
     */
    protected $filename = null;

    /**
     *  An array of generated mutations to be sequentially tested
     *
     * @var array
     */
    protected $mutations = [];

    /**
     * Array of Mutators currently enabled to generate mutations
     *
     * @var array
     */
    protected $mutators = [
        // Booleans
        '\Humbug\Mutator\Boolean\True',
        '\Humbug\Mutator\Boolean\False',
        '\Humbug\Mutator\Boolean\LogicalNot',
        '\Humbug\Mutator\Boolean\LogicalAnd',
        '\Humbug\Mutator\Boolean\LogicalOr',
        '\Humbug\Mutator\Boolean\LogicalLowerAnd',
        '\Humbug\Mutator\Boolean\LogicalLowerOr',
        // Conditional Boundaries
        '\Humbug\Mutator\ConditionalBoundary\GreaterThan',
        '\Humbug\Mutator\ConditionalBoundary\GreaterThanOrEqualTo',
        '\Humbug\Mutator\ConditionalBoundary\LessThan',
        '\Humbug\Mutator\ConditionalBoundary\LessThanOrEqualTo',
        // Binary Arithmetic
        '\Humbug\Mutator\Arithmetic\Addition',
        '\Humbug\Mutator\Arithmetic\Subtraction',
        '\Humbug\Mutator\Arithmetic\Multiplication',
        '\Humbug\Mutator\Arithmetic\Division',
        '\Humbug\Mutator\Arithmetic\Modulus',
        '\Humbug\Mutator\Arithmetic\Exponentiation',
        '\Humbug\Mutator\Arithmetic\PlusEqual',
        '\Humbug\Mutator\Arithmetic\MinusEqual',
        '\Humbug\Mutator\Arithmetic\MulEqual',
        '\Humbug\Mutator\Arithmetic\DivEqual',
        '\Humbug\Mutator\Arithmetic\ModEqual',
        '\Humbug\Mutator\Arithmetic\PowEqual',
        '\Humbug\Mutator\Arithmetic\BitwiseAnd',
        '\Humbug\Mutator\Arithmetic\BitwiseOr',
        '\Humbug\Mutator\Arithmetic\BitwiseXor',
        '\Humbug\Mutator\Arithmetic\Not',
        '\Humbug\Mutator\Arithmetic\ShiftRight',
        '\Humbug\Mutator\Arithmetic\ShiftLeft',
        // Increments
        '\Humbug\Mutator\Increment\Increment',
        '\Humbug\Mutator\Increment\Decrement',
        // Negation of Conditionals
        '\Humbug\Mutator\ConditionalNegation\Equal',
        '\Humbug\Mutator\ConditionalNegation\NotEqual',
        '\Humbug\Mutator\ConditionalNegation\Identical',
        '\Humbug\Mutator\ConditionalNegation\NotIdentical',
        '\Humbug\Mutator\ConditionalNegation\GreaterThan',
        '\Humbug\Mutator\ConditionalNegation\GreaterThanOrEqualTo',
        '\Humbug\Mutator\ConditionalNegation\LessThan',
        '\Humbug\Mutator\ConditionalNegation\LessThanOrEqualTo',
        // Explicit Numbers
        '\Humbug\Mutator\Number\Integer',
        '\Humbug\Mutator\Number\Float',
        // Return Values
        '\Humbug\Mutator\ReturnValue\This',
        '\Humbug\Mutator\ReturnValue\True',
        '\Humbug\Mutator\ReturnValue\False',
        '\Humbug\Mutator\ReturnValue\Integer',
        '\Humbug\Mutator\ReturnValue\Float',
        '\Humbug\Mutator\ReturnValue\NewObject',
        '\Humbug\Mutator\ReturnValue\FunctionCall',
    ];

    /**
     * Constructor; sets name and relative path of the file being mutated
     *
     * @param string $filename
     */
    public function __construct($filename = null)
    {
        $this->setFilename($filename);
    }

    /**
     * Based on the current file, generate mutations
     *
     * @return void
     */
    public function generate()
    {
        $source = file_get_contents($this->getFilename());
        $tokens = Tokenizer::getTokens($source);
        $lineNumber = 1;
        $methodName = '???';
        $className = '???';
        foreach ($tokens as $index => $token) {

            if (is_array($token) && $token[0] == Tokenizer::T_NEWLINE) {
                $lineNumber = $token[2] + 1;
                continue;
            }

            $namespace = ''; // TODO: Looks to be well out of place...

            if (is_array($token) && $token[0] == T_NAMESPACE) {
                for ($j=$index+1; $j<count($tokens); $j++) {
                    if ($tokens[$j][0] == T_STRING) {
                        $namespace .= '\\' . $tokens[$j][1];
                    } elseif ($tokens[$j] == '{' || $tokens[$j] == ';') {
                        break;
                    }
                }
                continue;
            }

            if (is_array($token) && ($token[0] == T_CLASS || $token[0] == T_INTERFACE)
            && $tokens[$index-1][0] !== T_DOUBLE_COLON) {
                $className = $namespace . '\\' . $tokens[$index+2][1];
                continue;
            }

            if (is_array($token) && $token[0] == T_FUNCTION) {
                if (!isset($tokens[$index+2][1])) {
                    continue; // ignore closure
                }
                $methodName = $tokens[$index+2][1];
                continue;
            }

            foreach ($this->mutators as $mutator) {
                if ($mutator::mutates($tokens, $index)) {
                    $this->mutations[] = [
                        'file'          => $this->getFilename(),
                        'class'         => $className,
                        'method'        => $methodName,
                        'index'         => $index,
                        'mutator'      => $mutator,
                        'line'          => $lineNumber
                    ];
                }
            }
        }
        return $this;
    }

    /**
     * Cleanup routines
     */
    public function cleanup()
    {
        unset($this->mutations);
    }

    /**
     * Set the file path of the file which is currently being assessed for
     * mutations.
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
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
     * Get an array of Class & Method indexed mutations containing the mutated
     * token and that token's index in the method's block code.
     *
     * @return array
     */
    public function getMutations()
    {
        return $this->mutations;
    }

    /**
     * Fare thee well runkit...
     */
    protected function parseMutables()
    {
        $source = file_get_contents($this->getFilename());
        $tokens = Tokenizer::getTokens($source);

        $inblock = false;
        $inarg = false;
        $collectArg = false;
        $inclosure = false;
        $curlycount = 0;
        $roundcount = 0;
        $blockTokens = [];
        $argTokens = [];
        $methods = [];
        $cleanMutable = [
            'replace'   => [],
            'args'      => ''
        ];
        $mutable = $cleanMutable;
        $static = false;
        $staticClassCapture = true;
        $namespace = '';
        $classesUsed = [];
        $refsToReplace = [];

        $lineNumber = 1;

        foreach ($tokens as $index => $token) {
            
            /**
             * Check for T_NEWLINE marker tokens (interpolated by getTokens())
             */
            if (is_array($token) && $token[0] == self::T_NEWLINE) {
                $lineNumber = $token[2] + 1; // it's a newline count; add one for line number
                //continue;
            }

            /**
             * Check whether token is indicating entry into a static method
             * Helps not to trip over the varied uses for T_STATIC...
             */
            if(is_array($token) && $token[0] == T_STATIC && $staticClassCapture === true && !$inblock) {
                $static = true;
                $staticClassCapture = false;
                continue;
            }

            /**
             * Set the current Namespace when detected
             */
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $namespace = '';
                for ($j=$index+1; $j<count($tokens); $j++) {
                    if ($tokens[$j][0] == T_STRING) {
                        $namespace .= '\\' . $tokens[$j][1];
                    } elseif ($tokens[$j] == '{' || $tokens[$j] == ';') {
                        break;
                    }
                }
                continue;
            }

            /**
             * Collect an array of all namespace/class imports. These are used to
             * resolve later references to fully qualified names as these are
             * required by runkit parsing.
             *
             * This omits any imported functions or constants.
             *
             * TODO: Need to implement alias handling.
             */
            if (is_array($token) && $token[0] == T_USE) {
                $used = '';
                for ($j=$index+1; $j<count($tokens); $j++) {
                    if ($tokens[$j][0] == T_FUNCTION || $tokens[$j][0] == T_CONST) {
                        break;
                    }
                    if ($tokens[$j][0] == T_STRING) {
                        $used .= '\\' . $tokens[$j][1];
                    } elseif ($tokens[$j] == ',') {
                        $classesUsed[] = $used;
                        $used = '';
                    } elseif ($tokens[$j] == ';') {
                        $classesUsed[] = $used;
                        break;
                    }
                }
                continue;
            }

            /**
             * Runkit just loathes an unresolved class name...
             *
             * Grab any static class refs and resolve them against the current
             * set of namespaces and imported references to fully qualified names
             * for later replacement.
             *
             * This does not terminate the current token loop.
             */
            if (is_array($token) && $token[0] == T_DOUBLE_COLON) {
                $ref = '';
                for ($j=$index-1; ; $j--) {
                    if (is_array($tokens[$j])
                    && ($tokens[$j][1] == 'self' || $tokens[$j][1] == 'parent' || $tokens[$j][0] == T_STATIC
                    || ($tokens[$j][0] !== T_STRING && $tokens[$j][0] !== T_NS_SEPARATOR))) {
                        break;
                    } elseif (!is_array($tokens[$j])) {
                        break;
                    } else {
                        $ref = $tokens[$j][1] . $ref;
                    }
                }
                if ($ref !== '' && $ref[0] !== '\\' && !in_array($ref, $refsToReplace)) {
                    $resolved = false;
                    if (!empty($classesUsed)) {
                        $parts = explode('\\', $ref);
                        $ns = '';
                        for ($i=0; $i < count($parts); $i++) {
                            if ($i > 0) $ns .= '\\';
                            $ns .= $parts[$i];
                            foreach ($classesUsed as $used) {
                                if (preg_match("%" . preg_quote($ns) . "$%", $used)) {
                                    $mutable['replace'][] = [$ref, $used];
                                    $refsToReplace[] = $ref;
                                    $resolved = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    if (!$resolved) {
                        $mutable['replace'][] = [$ref, $namespace.'\\'.$ref];
                        $refsToReplace[] = $ref;
                    }
                }
            }

            /**
             * Grab any 'new' class refs and resolve them against the current
             * set of namespaces and imported references to fully qualified names
             * for later replacement.
             *
             * This does not terminate the current token loop.
             */
            if (is_array($token) && $token[0] == T_NEW) {
                $ref = '';
                for ($j=$index+1; ; $j++) {
                    if (is_array($tokens[$j])
                    && ($tokens[$j][1] == 'self' || $tokens[$j][1] == 'parent' || $tokens[$j][0] == T_STATIC
                    || ($tokens[$j][0] !== T_STRING && $tokens[$j][0] !== T_NS_SEPARATOR && $tokens[$j][0] !== T_WHITESPACE))) {
                        break;
                    } elseif (!is_array($tokens[$j])) {
                        break;
                    } elseif ($tokens[$j][0] == T_STRING || $tokens[$j][0] == T_NS_SEPARATOR) {
                        $ref .= $tokens[$j][1];
                    }
                }
                if ($ref !== '' && $ref[0] !== '\\' && !in_array($ref, $refsToReplace)) {
                    $resolved = false;
                    if (!empty($classesUsed)) {
                        $parts = explode('\\', $ref);
                        $ns = '';
                        for ($i=0; $i < count($parts); $i++) {
                            if ($i > 0) $ns .= '\\';
                            $ns .= $parts[$i];
                            foreach ($classesUsed as $used) {
                                if (preg_match("%" . preg_quote($ns) . "$%", $used)) {
                                    $mutable['replace'][] = [$ref, $used];
                                    $refsToReplace[] = $ref;
                                    $resolved = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    if (!$resolved) { // use default namespace
                        $mutable['replace'][] = [$ref, $namespace.'\\'.$ref];
                        $refsToReplace[] = $ref;
                    }
                }
            }

            /**
             * Grab the class being defined and set its fully qualified name.
             */
            if (is_array($token) && ($token[0] == T_CLASS || $token[0] == T_INTERFACE)
            && $tokens[$index-1][0] !== T_DOUBLE_COLON) {
                $className = $namespace . '\\' . $tokens[$index+2][1];
                $staticClassCapture = false;
                continue;
            }

            /**
             * Get name of a method if detected (or a closure) and set the appropriate
             * flags. We don't want or need the body of a closure but keep track of
             * entries and exists from them so there's no confusion.
             *
             * TODO: Exclude normal functions.
             */
            if (is_array($token) && $token[0] == T_FUNCTION) {
                if (!isset($tokens[$index+2][1])) { // closure not followed by a name
                    $inclosure = true;
                    $inarg = true;
                    continue;
                }
                $methodName = $tokens[$index+2][1];
                $inarg = true;
                $mutable += [
                    'file' => $this->getFilename(),
                    'class' => $className,
                    'method' => $methodName,
                    'classesUsed' => $classesUsed
                ];
                continue;
            }

            /**
             * Collect the parameter string and store it on the current mutable
             * assuming we are not in a closure (otherwise they should go towards)
             * the current method's code block.
             *
             * TODO: Check we are adding closure args back to method block!
             */
            if ($inarg) {
                if ($token == '(') {
                    if ($roundcount == 0) {
                        $collectArg = true;
                        $roundcount += 1;
                        continue;
                    }
                    $roundcount += 1;
                } elseif ($token == ')') {
                    $roundcount -= 1;
                }
                if ($roundcount == 0 && $collectArg) {
                    if (count($argTokens) > 0) {
                        if (!$inclosure) {
                            $mutable['args'] = Tokenizer::reconstructFromTokens($argTokens);
                        }
                    }
                    $argTokens = [];
                    $inarg = false;
                    $inblock = true;
                    $collectArg = false;
                    continue;
                } elseif ($collectArg) {
                    $argTokens[] = $token;
                }
                continue;
            }

            // TODO: handle closure uses phrase???

            /**
             * Hoover up all tokens comprising the method's block code.
             *
             * We'll also assemble a list of class/interface refs that need to be
             * replace with their fully qualified names once the block code is
             * complete. The replacements are handled by the runkit class.
             */
            if ($inblock) {
                /**
                 * Track method boundary and collect code block tokens. Once done
                 * reset the relevant flags and arrays. The block tokens export
                 * the line number.
                 *
                 * Bearing in mind that curly bracket tokens have variations...
                 */
                if ($token == '{'
                || (is_array($token) && $token[0] == T_CURLY_OPEN)) {
                    $curlycount += 1;
                } elseif ($token == '}') {
                    $curlycount -= 1;
                }
                if ($curlycount == 1 && $token == '{') {
                    continue;
                } elseif ($curlycount >= 1) {
                    if (is_array($token) && $token[0] == T_METHOD_C) {
                        continue;
                    }
                    $blockTokens[] = [
                        'token' => $token,
                        'line'  => $lineNumber
                    ];
                } elseif ($curlycount == 0 && count($blockTokens) > 0) {
                    if (!$inclosure) {
                        $mutable['tokens'] = $blockTokens;
                        $methods[] = $mutable;
                    } else {
                        $inclosure = false;
                    }
                    $mutable = $cleanMutable;
                    $blockTokens = [];
                    $refsToReplace = [];
                    $inblock = false;
                    $staticClassCapture = true;
                }
            }
        }
        return $methods;
    }
    
}
