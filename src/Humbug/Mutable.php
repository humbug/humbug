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

class Mutable
{

    const T_NEWLINE = -1;

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
     *  Array of mutable elements located in file
     *
     * @var array
     */
    protected $mutables = [];

    protected $mutators = [
        '\Humbug\Mutation\BooleanAnd',
        '\Humbug\Mutation\BooleanFalse',
        '\Humbug\Mutation\BooleanOr',
        '\Humbug\Mutation\BooleanTrue',
        '\Humbug\Mutation\ConditionGreaterThan',
        '\Humbug\Mutation\ConditionGreaterThanOrEqualTo',
        '\Humbug\Mutation\ConditionLessThan',
        '\Humbug\Mutation\ConditionLessThanOrEqualTo',
        '\Humbug\Mutation\OperatorAddition',
        '\Humbug\Mutation\OperatorSubtraction',
        '\Humbug\Mutation\OperatorIncrement',
        '\Humbug\Mutation\OperatorDecrement'
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
        $this->mutables = $this->parseMutables();
        $this->parseTokensToMutations($this->mutables);
        return $this;
    }

    /**
     * Cleanup routines
     */
    public function cleanup()
    {
        unset($this->mutations, $this->mutables);
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
     * Get an array of method metainfo in tokenised form representing methods
     * which are capable of being mutated. Note: This does not guarantee they
     * will be mutated since this depends on the scope of supported mutations.
     *
     * @return array
     */
    public function getMutables()
    {
        return $this->mutables;
    }

    /**
     * Check whether the current file will contain a mutation of the given type
     *
     * @param string $type The mutation type as documented
     * @return bool
     */
    public function hasMutation($type)
    {
        $typeClass = '\\Humbug\\Mutation\\' . $type;
        // Don't ask...
        $mutations = array_values(array_values(array_values($this->getMutations())));
        foreach ($mutations as $mutation) {
            if ($mutation instanceof $typeClass) {
                return true;
            }
        }
        return false;
    }

    /**
     * Based on the internal array of mutable methods, generate another
     * internal array of supported mutations accessible using getMutations().
     *
     * @param array $mutables
     * @return void
     */
    protected function parseTokensToMutations(array $mutables)
    {
        foreach ($mutables as $method) {
            if (!isset($method['tokens']) || empty($method['tokens'])) {
                continue;
            }
            /**
             * Get non-interpolated tokens just as should arrive from token_get_all()
             */
            $cleanTokens = [];
            foreach ($method['tokens'] as $index=>$t) {
                $cleanTokens[$index] = $t['token'];
            }
            /**
             * Check all tokens and see which can be mutated. Keep the viable
             * mutations ready to spawn later.
             */
            foreach ($method['tokens'] as $index=>$token) {
                foreach ($this->mutators as $mutator) {
                    if ($mutator::mutates($cleanTokens, $index)) {
                        $this->mutations[] = [
                            'replace'       => $method['replace'],
                            'args'          => $method['args'],
                            'file'          => $method['file'],
                            'class'         => $method['class'],
                            'method'        => $method['method'],
                            'classesUsed'   => $method['classesUsed'],
                            'tokens'        => $cleanTokens,
                            'index'         => $index,
                            'mutation'      => new $mutator($this->getFilename()),
                            'line'          => $token['line']
                        ];
                    }
                }
            }
            
        }
    }

    /**
     * Parse given file into classes, method signatures and method bodies. We need
     * to track line numbers to allow cross referencing between code coverage data
     * and covering tests which we can use to run only those tests which target
     * a given mutated line of code.
     *
     * Also, a rabbit hole...
     *
     * TODO: Account for all variable spacing between tokens
     *
     * @return array
     */
    protected function parseMutables()
    {
        $source = file_get_contents($this->getFilename());
        $tokens = $this->getTokens($source);

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
                            $mutable['args'] = $this->reconstructFromTokens($argTokens);
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

    /**
     * Get tokens use token_get_all() but post process to interpolate new line
     * markers so we can check the line number of each token.
     *
     * @param string $source
     * @return array
     */
    protected function getTokens($source)
    {
        $newline = 0;
        $tokens = token_get_all($source);
        foreach ($tokens as $token) {
            $tname = is_array($token) ? $token[0] : null;
            $tdata = is_array($token) ? $token[1] : $token;
            if ($tname == T_CONSTANT_ENCAPSED_STRING) {
                $ntokens[] = [$tname, $tdata];
                continue;
            } elseif (substr($tdata, 0, 2) == '/*') {
                $ntokens[] = [$tname, $tdata];
                $split = preg_split("%(\r\n|\n)%", $tdata, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                foreach ($split as $value) {
                    if ($value == "\r\n" || $value == "\n") {
                        $newline++;
                    }
                }
                continue;
            }
            $split = preg_split("%(\r\n|\n)%", $tdata, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            foreach ($split as $data) {
                if ($data == "\r\n" || $data == "\n") {
                    $newline++;
                    $ntokens[] = [self::T_NEWLINE, $data, $newline];
                } else {
                    $ntokens[] = is_array($token) ? [$tname, $data] : $data;
                }
            }
        }
        return $ntokens;
    }

    /**
     * Reconstruct a string of source code from its constituent tokens
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
