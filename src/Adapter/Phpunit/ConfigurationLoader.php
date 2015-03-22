<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 *
 * @author     rafal.wartalski@gmail.com
 */

namespace Humbug\Adapter\Phpunit;

class ConfigurationLoader
{
    /**
     * @param string $configFile
     * @return \DOMDocument
     */
    public function load($configFile)
    {
        $oldValue = libxml_disable_entity_loader(true);
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(file_get_contents($configFile));
        libxml_disable_entity_loader($oldValue);
        return $dom;
    }
}
