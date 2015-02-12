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
        $dom = new \DOMDocument();

        $dom->appendChild($dom->createElement('phpunit'));

        $dom->documentElement->setAttribute('bootstrap', '/test/bootstrap.php');

        $xmlConfiguration = new XmlConfiguration($dom);

        $this->assertTrue($xmlConfiguration->hasBootstrap());
        $this->assertEquals('/test/bootstrap.php', $xmlConfiguration->getBootstrap());
    }
} 