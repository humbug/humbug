<?php

class FailTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group PHPUnitRunnerTesting
     */
    public function testSomeFail()
    {
        $this->assertTrue(false);
    }
}
