<?php

namespace Humbug\Adapter\Phpunit;

class ConfigurationLoader
{
    public function load($configFile)
    {
        return new XmlConfiguration(new \DOMDocument());
    }
}