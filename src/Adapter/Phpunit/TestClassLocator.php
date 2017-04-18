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

    private $map = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->dom = $this->loadXml();
        $this->xpath = new \DOMXPath($this->dom);
    }

    /**
     * @param string $class
     * @return string
     */
    public function locate($class)
    {
        if (isset($this->map[$class])) {
            return $this->map[$class];
        }
        $this->map[$class] = $this->xpath->evaluate(sprintf(
            "string((//testcase[@class='%s'])[1]/@file)",
            $class
        ));
        return $this->map[$class];
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
