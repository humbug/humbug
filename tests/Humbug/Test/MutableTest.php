<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test;

use Humbug\Mutable;
use Humbug\Mutator;

class MutableTest extends \PHPUnit_Framework_TestCase
{

    protected $root = '';

    public function setUp()
    {
        $this->root = dirname(__FILE__) . '/_files/root/base2/library';
    }

    public function testShouldMaintainFilePathInfoOncePassedInConstructor()
    {
        $file = new Mutable($this->root . '/foo.php');
        $this->assertEquals($this->root . '/foo.php', $file->getFilename());
    }

    public function testShouldNotHaveMutationsBeforeGeneration()
    {
        $file = new Mutable($this->root . '/math1.php');
        $this->assertEquals([], $file->getMutations());
    }

    public function testShouldNotHaveDetectedMutablesBeforeGeneration()
    {
        $file = new Mutable($this->root . '/math1.php');
        $this->assertEquals([], $file->getMutations());
    }

    public function testShouldNotGenerateMutablesForEmptyClass()
    {
        $file = new Mutable($this->root . '/math0.php');
        $file->generate();
        $this->assertEquals([], $file->getMutations());
    }

    public function testShouldNotgenerateForEmptyClass()
    {
        $file = new Mutable($this->root . '/math0.php');
        $file->generate();
        $this->assertEquals([], $file->getMutations());
    }

    public function testShouldNotGenerateMutationsIfOnlyEmptyMethodsInClass()
    {
        $file = new Mutable($this->root . '/math00.php');
        $file->generate();
        $this->assertEquals([], $file->getMutations());
    }

    public function testShouldGenerateMutablesEvenIfMethodBodyIsNotViable()
    {
        $file = new Mutable($this->root . '/math000.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals(['file','class','method','args','tokens'],array_keys($return[0]));
    }

    public function testShouldNotGenerateMutablesIfMethodBodyIsNotViable()
    {
        $file = new Mutable($this->root . '/math000.php');
        $file->generate();
        $this->assertEquals([], $file->getMutations());
    }

    public function testShouldGenerateAMutationIfPossible()
    {
        $file = new Mutable($this->root . '/math1.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals(['file','class','method','index','mutator', 'line'],array_keys($return[0]));
    }

    public function testShouldReturnMutationsAsMutantObjectWrappers()
    {
        $file = new Mutable($this->root . '/math1.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertTrue($return[0]['mutator'] instanceof Mutation\MutationAbstract);
    }

    public function testShouldDetectMutablesForClassesInSameFileSeparately()
    {
        $file = new Mutable($this->root . '/mathx2.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals('\Math2', $return[1]['class']);
    }

    public function testShouldDetectMutationsForClassesInSameFileSeparately()
    {
        $file = new Mutable($this->root . '/mathx2.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals('\Math2', $return[1]['class']);
    }


    // Ensure correct class is returned as a mutation


    public function testShouldGenerateAdditionOperatorMutationWhenPlusSignDetected()
    {
        $file = new Mutable($this->root . '/math1.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals('\Humbug\Mutator\Arithmetic\Addition', $return[0]['mutator']);
    }

    public function testShouldGenerateSubstractionOperatorMutationWhenMinusSignDetected()
    {
        $file = new Mutable($this->root . '/math2.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals('\Humbug\Mutator\Arithmetic\Substraction', $return[0]['mutator']);
    }

    public function testShouldGenerateIncrementOperatorMutationWhenPostIncrementDetected()
    {
        $file = new Mutable($this->root . '/math3.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals('\Humbug\Mutator\Increment\Increment', $return[0]['mutator']);
    }

    public function testShouldGenerateIncrementOperatorMutationWhenPreIncrementDetected()
    {
        $file = new Mutable($this->root . '/math4.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals('\Humbug\Mutator\Increment\Increment', $return[0]['mutator']);
    }

    public function testShouldGenerateBooleanTrueMutationWhenBoolTrueDetected()
    {
        $file = new Mutable($this->root . '/bool1.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals('\Humbug\Mutator\ReturnValue\True', $return[0]['mutator']);
    }

    public function testShouldGenerateBooleanFalseMutationWhenBoolFalseDetected()
    {
        $file = new Mutable($this->root . '/bool2.php');
        $file->generate();
        $return = $file->getMutations();
        $this->assertEquals('\Humbug\Mutator\ReturnValue\False', $return[0]['mutator']);
    }

    public function testShoultNotGenerateMutableOnArrayConcatenation()
    {
        $file = new Mutable($this->root . '/array1.php');
        $file->generate();
        $this->assertEquals([], $file->getMutations());
    }

    /**
     * Covers bug where Mutable may incorrectly parse a method and omit the first
     * opening bracket in an IF clause, leading to syntax errors when
     * attempting to add the new method block via runkit
     *
     * @group MM1
     */
    public function testCreatesAccurateMapOfIfClausesSingleNonStaticMethod()
    {
        $file = new Mutable(dirname(__FILE__) . '/_files/IfClause.php');
        $file->generate();
        $mutations = $file->getMutations();
        $mutation = $mutations[0];
        $this->assertEquals(dirname(__FILE__) . '/_files/IfClause.php', $mutation['file']);
        $this->assertEquals('\Some_Class_With_If_Clause_In_Method', $mutation['class']);
        $this->assertEquals('_getSession', $mutation['method']);
        $block = <<<BLOCK

        static \$session = null;
        if (\$session === null) {
            \$session = new Zend_Session_Namespace(
                \$this->getSessionNamespace(), true
            );
        }

BLOCK;
        $this->assertEquals($block, $this->_reconstructFromTokens($mutation['tokens']));
    }

    /**
     * Reconstruct a string of source code from its constituent tokens
     *
     * @param array $tokens
     * @return string
     */
    protected function _reconstructFromTokens(array $tokens)
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
