<?php

class ErrorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @group PHPUnitRunnerTesting
     */
    public function testSomeError()
    {
        trigger_error('error', E_USER_NOTICE);
    }
}
