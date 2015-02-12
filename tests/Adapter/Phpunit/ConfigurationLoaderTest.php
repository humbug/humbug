<?php

namespace Humbug\Test\Adapter\Phpunit;

use Humbug\Adapter\Phpunit\ConfigurationLoader;

class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldLoadDomDocument()
    {
        $configFile = '';

        $loader = new ConfigurationLoader();

        $domDocument = $loader->load($configFile);

        $this->assertInstanceOf('\DomDocument', $domDocument);
        $this->assertEquals(false, $domDocument->preserveWhiteSpace);
        $this->assertEquals(true, $domDocument->formatOutput);
    }
} 