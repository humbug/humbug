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
} 