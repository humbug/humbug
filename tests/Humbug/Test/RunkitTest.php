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

use Humbug\Runkit;
use Humbug\Mutation;

class RunkitTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->root = __DIR__ . '/_files';
    }
    
    public function testShouldApplyGivenMutationsUsingRunkitToReplaceEffectedMethods()
    {
        $mutation = array(
            'file' => $this->root . '/runkit/Math1.php',
            'class' => 'RunkitTest_Math1',
            'method' => 'add',
            'args' => '$op1,$op2',
            'tokens' => array(array(335,'return',7), array(309,'$op1',7), '+', array(309,'$op2',7), ';'),
            'index' => 2,
            'mutation' => new Mutation\OperatorAddition($this->root . '/runkit/Math1.php')
        );
        require_once $mutation['file'];
        $runkit = new Runkit;
        $runkit->applyMutation($mutation);
        $math = new \RunkitTest_Math1;
        $this->assertEquals(0, $math->add(1,1));
        $runkit->reverseMutation($mutation);
    }

    public function testShouldRevertToOriginalMethodBodyWhenRequested()
    {
        $mutation = array(
            'file' => $this->root . '/runkit/Math1.php',
            'class' => 'RunkitTest_Math1',
            'method' => 'add',
            'args' => '$op1,$op2',
            'tokens' => array(array(335,'return',7), array(309,'$op1',7), '+', array(309,'$op2',7), ';'),
            'index' => 2,
            'mutation' => new Mutation\OperatorAddition($this->root . '/runkit/Math1.php')
        );
        require_once $mutation['file'];
        $runkit = new Runkit;
        $runkit->applyMutation($mutation);
        $math = new \RunkitTest_Math1;
        $runkit->reverseMutation($mutation);
        $this->assertEquals(2, $math->add(1,1));
    }

    public function testShouldApplyGivenMutationsUsingRunkitToReplaceEffectedStaticMethods()
    {
        $mutation = array(
            'file' => $this->root . '/runkit/Math2.php',
            'class' => 'RunkitTest_Math2',
            'method' => 'add',
            'args' => '$op1,$op2',
            'tokens' => array(array(335,'return',7), array(309,'$op1',7), '+', array(309,'$op2',7), ';'),
            'index' => 2,
            'mutation' => new Mutation\OperatorAddition($this->root . '/runkit/Math2.php')
        );
        require_once $mutation['file'];
        $runkit = new Runkit;
        $runkit->applyMutation($mutation);
        $this->assertEquals(0, \RunkitTest_Math2::add(1,1));
        $runkit->reverseMutation($mutation);
    }

    public function testShouldRevertToOriginalStaticMethodBodyWhenRequested()
    {
        $mutation = array(
            'file' => $this->root . '/runkit/Math2.php',
            'class' => 'RunkitTest_Math2',
            'method' => 'add',
            'args' => '$op1,$op2',
            'tokens' => array(array(335,'return',7), array(309,'$op1',7), '+', array(309,'$op2',7), ';'),
            'index' => 2,
            'mutation' => new Mutation\OperatorAddition($this->root . '/runkit/Math2.php')
        );
        require_once $mutation['file'];
        $runkit = new Runkit;
        $runkit->applyMutation($mutation);
        $runkit->reverseMutation($mutation);
        $this->assertEquals(2, \RunkitTest_Math2::add(1,1));
    }
}

class StubHumbugMutation1 extends Mutation\MutationAbstract
{
    public function getMutation(array $tokens, $index){}
}
