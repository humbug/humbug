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

namespace Humbug\Test\Mutator\FunctionCall;

use Humbug\Mutator;

class FunctionCallTest extends \PHPUnit_Framework_TestCase
{

    public function testMutatesWithValueReturnTrue()
    {
        $tokens = [
            0 => [
                0 => 337,
                1 => 'return',
            ],
            1 => [
                0 => 377,
                1 => ' ',
            ],
            2 => [
                0 => 308,
                1 => 'true',
            ],
            3 => ';',
        ];
        $this->assertFalse(Mutator\ReturnValue\FunctionCall::mutates($tokens, 0));
    }
}
