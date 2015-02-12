<?php

namespace Humbug\Test\Adapter\Phpunit\XmlConfiguration;

use Humbug\Adapter\Phpunit\XmlConfiguration\AcceleratorListener;

class AcceleratorListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeVisitor()
    {
        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor', new AcceleratorListener());
    }

    public function testShouldUpdateDomNode()
    {
        $dom = new \DOMDocument();
        $domElement = $dom->createElement('listener');
        $dom->appendChild($domElement);

        $acceleratorListener = new AcceleratorListener();

        $acceleratorListener->visitElement($domElement);

        $this->assertEquals('\MyBuilder\PhpunitAccelerator\TestListener', $domElement->getAttribute('class'));

        $boolean = (new \DOMXPath($dom))->query('/listener/arguments/boolean');

        $this->assertEquals(1, $boolean->length);
        $this->assertEquals('true', $boolean->item(0)->nodeValue);
    }
}
