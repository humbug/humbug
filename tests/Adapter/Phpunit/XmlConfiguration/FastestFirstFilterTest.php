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

use Humbug\Adapter\Phpunit\XmlConfiguration\FastestFirstFilter;

class FastestFirstFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeVisitor()
    {
        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor', new FastestFirstFilter('x'));
    }

    public function testShouldUpdateDomElementWithEmptyArgumentsNode()
    {
        $dom = new \DOMDocument();
        $domElement = $dom->createElement('object');
        $dom->appendChild($domElement);

        (new FastestFirstFilter('/path/to/logfile'))->visitElement($domElement);

        $this->assertEquals('\Humbug\Phpunit\Filter\TestSuite\FastestFirstFilter', $domElement->getAttribute('class'));

        $xpath = new \DOMXPath($dom);

        $this->assertEquals(1, $xpath->evaluate('count(/object/arguments)'));
        $this->assertEquals(1, $xpath->evaluate('count(/object/arguments/string)'));

        $this->assertEquals('/path/to/logfile', $xpath->query('/object/arguments/string')->item(0)->nodeValue);
    }
}
