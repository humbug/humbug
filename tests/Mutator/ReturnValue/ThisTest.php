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

use Humbug\Mutator\ReturnValue\This;
use Humbug\Utility\Tokenizer;

class ThisTest extends \PHPUnit\Framework\TestCase
{
    public function testMutatesWithValueReturnTrue()
    {
        $content = '<?php return true;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertFalse(This::mutates($tokens, 0));
    }

    public function testMutatesWithValueReturnThisNoParams()
    {
        $content = '<?php return $this;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        $this->assertTrue(This::mutates($tokens, 0));
    }

    public function testGetsMutationSettingReturnValueNullAndPreservingObjectInstantiation()
    {
        $content = '<?php return $this;';
        $tokens = Tokenizer::getTokens($content);
        array_shift($tokens);
        This::getMutation($tokens, 0);
        $this->assertSame('return null;', Tokenizer::reconstructFromTokens($tokens));
    }
}
