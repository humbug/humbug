<?php

namespace Humbug\Test\Adapter;

use Humbug\Adapter\Locator;

class LocatorTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldLocateMany()
    {
        $dir = __DIR__ . '/../Adapter/_files';

        $locator = new Locator($dir);

        $actual = $locator->locateDirectories('php*');

        $expected = [
            realpath($dir . '/phpunit'),
            realpath($dir . '/phpunit-conf'),
            realpath($dir . '/phpunit2'),
        ];

        $this->assertEquals($expected, $actual);
    }
}
