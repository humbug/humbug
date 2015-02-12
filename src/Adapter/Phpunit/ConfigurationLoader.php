<?php

namespace Humbug\Adapter\Phpunit;

class ConfigurationLoader
{
    /**
     * @param string $configFile
     * @return \DOMDocument
     */
    public function load($configFile)
    {
        $dom = new \DOMDocument();

        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        return $dom;
    }
}