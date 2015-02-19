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

namespace Humbug\Test\Adapter\Phpunit\XmlConfiguration;

use Humbug\Adapter\Phpunit\XmlConfiguration\FilterListener;

class FilterListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeVisitor()
    {
        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor', new FilterListener([]));
    }

    public function testShouldUpdateDomElement()
    {
        $dom = new \DOMDocument();
        $domElement = $dom->createElement('listener');
        $dom->appendChild($domElement);

        (new FilterListener([]))->visitElement($domElement);

        $this->assertEquals('\Humbug\Phpunit\Listener\FilterListener', $domElement->getAttribute('class'));
        $this->assertEquals(0, (new \DOMXPath($dom))->evaluate('count(/listener/arguments)'));
    }

    public function testShouldAddArguments()
    {
        $arguments = [
            $this->createArgument(),
            $this->createArgument()
        ];

        $dom = new \DOMDocument();
        $domElement = $dom->createElement('listener');
        $dom->appendChild($domElement);

        (new FilterListener($arguments))->visitElement($domElement);

        $xpath = new \DOMXPath($dom);

        $this->assertEquals(1, $xpath->evaluate('count(/listener/arguments)'));
        $this->assertEquals(2, $xpath->evaluate('count(/listener/arguments/*)'));
    }

    private function createArgument()
    {
        $argument =
            $this->getMockBuilder('Humbug\Adapter\Phpunit\XmlConfiguration\ObjectVisitor')
                ->disableOriginalConstructor()
                ->getMock();

        $argument->expects($this->once())->method('visitElement')->with($this->isInstanceOf('\DOMElement'));

        return $argument;
    }


}
