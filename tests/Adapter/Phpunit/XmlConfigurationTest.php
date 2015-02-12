<?php

namespace Humbug\Test\Adapter\Phpunit;

use Humbug\Adapter\Phpunit\XmlConfiguration;

class XmlConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldHaveDom()
    {
        $dom = new \DOMDocument();
        $xmlConfiguration = new XmlConfiguration($dom);

        $this->assertSame($dom, $xmlConfiguration->dom);
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

} 