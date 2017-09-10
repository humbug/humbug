<?php

class RequiresServerArgvTest extends \PHPUnit\Framework\TestCase
{
    public function testServerShouldHaveArgv()
    {
        $this->assertArrayHasKey('argv', $_SERVER);
        $this->assertArrayHasKey(0, $_SERVER['argv']);
        $this->assertContains('phpunit', $_SERVER['argv'][0]);
    }

    public function testServerArgvShouldContainPhpunit()
    {
        $this->assertContains('phpunit', $_SERVER['argv'][0]);
    }
}
