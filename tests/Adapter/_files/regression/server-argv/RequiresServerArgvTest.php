<?php

class RequiresServerArgvTest extends \PHPUnit_Framework_TestCase
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