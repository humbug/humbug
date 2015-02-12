<?php

namespace Humbug\Test\Adapter\Phpunit;

use Humbug\Adapter\Phpunit\XmlConfiguration;

class XmlConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldLocateConfiguration()
    {
        $directory = __DIR__ . '/../_files/phpunit-conf';

        $xmlConfiguration = new XmlConfiguration();

        $configurationFile = $xmlConfiguration->findConfigurationFile($directory);

        $expectedXmlPath = realpath($directory . '/phpunit.xml');

        $this->assertEquals($expectedXmlPath, $configurationFile);
    }

    public function testShouldLocateDistConfiguration()
    {
        $directory = __DIR__ . '/../_files/phpunit';

        $xmlConfiguration = new XmlConfiguration();

        $configurationFile = $xmlConfiguration->findConfigurationFile($directory);

        $expectedXmlPath = realpath($directory . '/phpunit.xml.dist');

        $this->assertEquals($expectedXmlPath, $configurationFile);
    }

    public function testShouldRiseExceptionWhileLocatingConfiguration()
    {
        $directory = __DIR__;

        $this->setExpectedException('\Humbug\Exception\RuntimeException');

        (new XmlConfiguration())->findConfigurationFile($directory);
    }
} 