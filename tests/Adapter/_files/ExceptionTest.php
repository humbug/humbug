<?php

class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group PHPUnitRunnerTesting
     */
    public function testSomeException()
    {
        throw new Exception('exception');
    }
}
