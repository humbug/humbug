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

use Humbug\Adapter\Phpunit\XmlConfiguration\IncludeOnlyFilter;

class IncludeOnlyFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeVisitor()
    {
        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor', new IncludeOnlyFilter());
    }

    public function testShouldUpdateDomElementWithEmptyArgumentsNode()
    {
        $dom = new \DOMDocument();
        $domElement = $dom->createElement('object');
        $dom->appendChild($domElement);

        (new IncludeOnlyFilter())->visitElement($domElement);

        $this->assertEquals('\Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter', $domElement->getAttribute('class'));

        $xpath = new \DOMXPath($dom);

        $this->assertEquals(1, $xpath->evaluate('count(/object/arguments)'));
        $this->assertEquals(0, $xpath->evaluate('count(/object/arguments/*)'));
    }

    public function testShouldUpdateArgumentNodesWithBatchOfStringNodes()
    {
        $dom = new \DOMDocument();
        $domElement = $dom->createElement('object');
        $dom->appendChild($domElement);

        $testSuites = [
            'FirstSuiteTest',
            'SecondSuiteTest'
        ];

        (new IncludeOnlyFilter($testSuites))->visitElement($domElement);

        $xpath = new \DOMXPath($dom);

        $this->assertEquals(2, $xpath->evaluate('count(/object/arguments/string)'));

        $this->assertEquals('FirstSuiteTest',  $xpath->query('/object/arguments/string')->item(0)->nodeValue);
        $this->assertEquals('SecondSuiteTest',  $xpath->query('/object/arguments/string')->item(1)->nodeValue);
    }
}
