<?php

class FailTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @group PHPUnitRunnerTesting
     */
    public function testSomeFail()
    {
        $this->assertTrue(false);
    }
}
