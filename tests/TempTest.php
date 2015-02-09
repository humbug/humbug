<?php

namespace Humbug\Test;


class TempTest extends \PHPUnit_Framework_TestCase
{
    public function testPassing()
    {
        $this->assertTrue(true);
    }

    public function testFail()
    {
        $this->fail();
    }
}
