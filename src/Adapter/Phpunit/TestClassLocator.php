<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter\Phpunit;

use Humbug\Container;

class TestClassLocator
{

    private $container;

    private $dom;

    private $xpath;

    /**
     * @param string $class
     * @return string
     */
    public function locate($class, Container $container)
    {
        if (is_null($this->container)) {
            $this->container = $container;
        }
        if (is_null($this->dom)) {
            $this->dom = $this->loadXml();
            $this->xpath = new \DOMXPath($this->dom);
        }
        $item = $this->xpath->query(sprintf(
            "(//testcase[class='%s'])[1]/@file",
            $class
        ));
        return $item;
    }

    private function loadXml()
    {
        $log = $this->container->getTempDirectory() . '/junit.humbug.xml';
        $oldValue = libxml_disable_entity_loader(true);
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML(file_get_contents($log));
        libxml_disable_entity_loader($oldValue);
        return $dom;
    }
}
