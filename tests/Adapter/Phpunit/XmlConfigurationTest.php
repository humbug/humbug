<?php

namespace Humbug\Test\Adapter\Phpunit;

use Humbug\Adapter\Phpunit\XmlConfiguration;

class XmlConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldThrowExceptionIfNoDocumentElementIsPresent()
    {
        $this->setExpectedException('\LogicException', 'No document element present. Document should not be empty!');

        $dom = new \DOMDocument();
        new XmlConfiguration($dom);
    }

    public function testShouldHaveBootstrap()
    {
        $dom = $this->createDomWithBootstrap();

        $xmlConfiguration = new XmlConfiguration($dom);

        $this->assertTrue($xmlConfiguration->hasBootstrap());
        $this->assertEquals('/test/bootstrap.php', $xmlConfiguration->getBootstrap());
    }

    public function testShouldUpdateBootstrap()
    {
        $dom = $this->createDomWithBootstrap();

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->setBootstrap('/test/new/bootstrap.php');

        $this->assertEquals('/test/new/bootstrap.php', $xmlConfiguration->getBootstrap());
    }

    /**
     * @return \DOMDocument
     */
    private function createDomWithBootstrap()
    {
        $dom = $this->createBaseDomDocument();

        $dom->documentElement->setAttribute('bootstrap', '/test/bootstrap.php');

        return $dom;
    }

    /**
     * @return \DOMDocument
     */
    private function createBaseDomDocument()
    {
        $dom = new \DOMDocument();

        $dom->appendChild($dom->createElement('phpunit'));

        return $dom;
    }

    public function testShouldTurnOffCacheTokens()
    {
        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->turnOffCacheTokens();

        $this->assertEquals('false', $dom->documentElement->getAttribute('cacheTokens'));
    }

    public function testShouldCleanupLoggers()
    {
        $dom = $this->createDocumentWithChildElement('logging');

        (new XmlConfiguration($dom))->cleanupLoggers();

        $this->assertEquals(0, (new \DOMXPath($dom))->evaluate('count(//logging)'));
    }

    public function testShouldCleanupFilters()
    {
        $dom = $this->createDocumentWithChildElement('filter');

        (new XmlConfiguration($dom))->cleanupFilters();

        $this->assertEquals(0, (new \DOMXPath($dom))->evaluate('count(/phpunit/filter)'));
    }

    public function testShouldCleanupListeners()
    {
        $dom = $this->createDocumentWithChildElement('listeners');

        (new XmlConfiguration($dom))->cleanupListeners();

        $this->assertEquals(0, (new \DOMXPath($dom))->evaluate('count(//listeners)'));
    }

    /**
     * @return \DOMDocument
     */
    private function createDocumentWithChildElement($childElement)
    {
        $dom = $this->createBaseDomDocument();

        $dom->documentElement->appendChild($dom->createElement($childElement));

        return $dom;
    }
} 