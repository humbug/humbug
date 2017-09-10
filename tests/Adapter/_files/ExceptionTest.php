<?php

class ExceptionTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @group PHPUnitRunnerTesting
     */
    public function testSomeException()
    {
        throw new Exception('exception');
    }
}
