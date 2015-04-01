<?php

/**
*
* @category   Humbug
* @package    Humbug
* @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
* @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
* @author     Thibaud Fabre
*/
namespace Humbug\Test\TestSuite\Mutant;

use Humbug\TestSuite\Mutant\Result;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStatusCodeReturnsCorrectCode()
    {
        $this->assertEquals(Result::TIMEOUT, Result::getStatusCode(true, true, true));
        $this->assertEquals(Result::TIMEOUT, Result::getStatusCode(false, false, true));
        $this->assertEquals(Result::TIMEOUT, Result::getStatusCode(true, false, true));
        $this->assertEquals(Result::TIMEOUT, Result::getStatusCode(false, true, true));

        $this->assertEquals(Result::ESCAPE, Result::getStatusCode(true, true, false));

        $this->assertEquals(Result::ERROR, Result::getStatusCode(true, false, false));
        $this->assertEquals(Result::ERROR, Result::getStatusCode(false, false, false));

        $this->assertEquals(Result::KILL, Result::getStatusCode(false, true, false));
    }

    public function getInvalidStatusCodes()
    {
        return [
            [ -1 ],
            [ 4 ],
            [ 10 ]
        ];
    }

    /**
     * @dataProvider getInvalidStatusCodes
     * @expectedException \InvalidArgumentException
     */
    public function testResultRejectsInvalidStatusCode($statusCode)
    {
        new Result(
            $this->prophesize('Humbug\Mutant')->reveal(),
            $statusCode,
            '',
            ''
        );
    }

    public function testResultReturnsCorrectStatusAndErrorOutput()
    {
        $result = new Result(
            $this->prophesize('Humbug\Mutant')->reveal(),
            Result::ERROR,
            '',
            'error'
        );

        $this->assertEquals(Result::ERROR, $result->getResult());
        $this->assertEquals('error', $result->getErrorOutput());
    }

    public function testResultReportsTimeoutStatusCorrectly()
    {
        $result = new Result(
            $this->prophesize('Humbug\Mutant')->reveal(),
            Result::TIMEOUT,
            '',
            ''
        );

        $this->assertTrue($result->isTimeout());
        $this->assertFalse($result->isError());
        $this->assertFalse($result->isEscape());
        $this->assertFalse($result->isKill());
    }

    public function testResultReportsEscapeStatusCorrectly()
    {
        $result = new Result(
            $this->prophesize('Humbug\Mutant')->reveal(),
            Result::ESCAPE,
            '',
            ''
        );

        $this->assertFalse($result->isTimeout());
        $this->assertFalse($result->isError());
        $this->assertTrue($result->isEscape());
        $this->assertFalse($result->isKill());
    }

    public function testResultReportsErrorStatusCorrectly()
    {
        $result = new Result(
            $this->prophesize('Humbug\Mutant')->reveal(),
            Result::ERROR,
            '',
            ''
        );

        $this->assertFalse($result->isTimeout());
        $this->assertTrue($result->isError());
        $this->assertFalse($result->isEscape());
        $this->assertFalse($result->isKill());
    }

    public function testResultReportsKillStatusCorrectly()
    {
        $result = new Result(
            $this->prophesize('Humbug\Mutant')->reveal(),
            Result::KILL,
            '',
            ''
        );

        $this->assertFalse($result->isTimeout());
        $this->assertFalse($result->isError());
        $this->assertFalse($result->isEscape());
        $this->assertTrue($result->isKill());
    }
}
