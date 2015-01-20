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
        '\Humbug\Mutator\Arithmetic\Substraction',
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
        //'\Humbug\Mutator\ReturnValue\True',
        //'\Humbug\Mutator\ReturnValue\False',
        '\Humbug\Mutator\ReturnValue\Integer',
        //'\Humbug\Mutator\ReturnValue\Float',
        '\Humbug\Mutator\ReturnValue\IntegerNegation',
        '\Humbug\Mutator\ReturnValue\FloatNegation',
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
        gc_collect_cycles();
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
}
