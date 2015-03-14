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

namespace Humbug\Test\Mutator\ReturnValue;

use Humbug\Mutator\ReturnValue\NewObject;
use Humbug\Utility\Tokenizer;

class NewObjectTest extends \PHPUnit_Framework_TestCase
{

    public function testMutatesWithValueReturnTrue()
    {
        $content = '<?php return true;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(NewObject::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnNewObjectNoParams()
    {
        $content = '<?php return new Foo;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(NewObject::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnNewObjectWithParams()
    {
        $content = '<?php return new Foo(1, "foo", 0.3);';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(NewObject::mutates($tokens, 0));
    }

    public function testGetsMutationSettingReturnValueNullAndPreservingObjectInstantiation()
    {
        $content = '<?php return new Foo(1, "foo", 0.3);';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        NewObject::getMutation($tokens, 0);
        $this->assertSame('new Foo(1, "foo", 0.3); return null;', Tokenizer::reconstructFromTokens($tokens));
    }
}
