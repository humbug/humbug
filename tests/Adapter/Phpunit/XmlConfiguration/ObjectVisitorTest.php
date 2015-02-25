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

use Humbug\Adapter\Phpunit\XmlConfiguration\ObjectVisitor;

class ObjectVisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var \DOMElement
     */
    private $visitedObject;

    /**
     * @var \DOMXPath
     */
    private $xpath;

    protected function setUp()
    {
        $this->dom = new \DOMDocument();
        $this->xpath = new \DOMXPath($this->dom);

        $this->visitedObject = $this->dom->createElement('object');
        $this->dom->appendChild($this->visitedObject);
    }

    public function testShouldBeVisitor()
    {
        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor', new ObjectVisitor('x'));
    }

    public function testShouldAddClassName()
    {
        $objectVisitor = new ObjectVisitor('This\Is\Test\ClassName');

        $objectVisitor->visitElement($this->visitedObject);

        $this->assertEquals('This\Is\Test\ClassName', $this->visitedObject->getAttribute('class'));
        $this->assertEquals(0, $this->xpath->evaluate('count(/object/*)'));
    }

    /**
     * @dataProvider booleanArguments
     */
    public function testShouldAddBooleanArgument($arguments, $expected)
    {
        $objectVisitor = new ObjectVisitor('This\Is\Test\ClassName', $arguments);

        $objectVisitor->visitElement($this->visitedObject);

        $booleanList = $this->xpath->query('/object/arguments/boolean');

        $this->assertEquals(1, $booleanList->length);
        $this->assertEquals($expected, $booleanList->item(0)->nodeValue);
    }

    public function booleanArguments()
    {
        return [
            [[true], 'true'],
            [[false], 'false']
        ];
    }

    public function testShouldAddStringArgument()
    {
        $objectVisitor = new ObjectVisitor('This\Is\Test\ClassName', ['string argument']);

        $objectVisitor->visitElement($this->visitedObject);

        $stringList = $this->xpath->query('/object/arguments/string');

        $this->assertEquals(1, $stringList->length);
        $this->assertEquals('string argument', $stringList->item(0)->nodeValue);
    }

    public function testShouldAddObjectArgument()
    {
        $objectArgument =
            $this->getMockBuilder('Humbug\Adapter\Phpunit\XmlConfiguration\ObjectVisitor')
                ->disableOriginalConstructor()
                ->getMock();

        $objectArgument->expects($this->once())->method('visitElement')->with($this->isInstanceOf('\DOMElement'));

        $objectVisitor = new ObjectVisitor('This\Is\Test\ClassName', [$objectArgument]);

        $objectVisitor->visitElement($this->visitedObject);

        $objectList = $this->xpath->query('/object/arguments/object');

        $this->assertEquals(1, $objectList->length);

    }

    public function testShouldAddManyDifferentArguments()
    {
        $arguments = [
            false,
            'string',
            new ObjectVisitor('TestClass'),
        ];

        $objectVisitor = new ObjectVisitor('This\Is\Test\ClassName', $arguments);

        $objectVisitor->visitElement($this->visitedObject);

        $argumentList = $this->xpath->query('/object/arguments[position()=1]/*');

        $this->assertEquals(3, $argumentList->length);
        $this->assertEquals('false', $argumentList->item(0)->nodeValue);
        $this->assertEquals('string', $argumentList->item(1)->nodeValue);

        $this->assertInstanceOf('\DOMElement', $argumentList->item(2));
        $this->assertEquals('object', $argumentList->item(2)->tagName);
    }

    public function testShouldRiseExceptionWhenArgumentTypeIsNotSupported()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $objectVisitor = new ObjectVisitor('This\Is\Test\ClassName', [new \stdClass()]);

        $objectVisitor->visitElement($this->visitedObject);
    }
}
