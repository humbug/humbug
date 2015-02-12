<?php

namespace Humbug\Test\Adapter\Phpunit;

use Humbug\Adapter\Phpunit\ConfigurationLoader;

class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldLoadXmlConfiguration()
    {
        $configFile = '';

        $loader = new ConfigurationLoader();

        $xmlConfiguration = $loader->load($configFile);

        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration', $xmlConfiguration);
    }
} 