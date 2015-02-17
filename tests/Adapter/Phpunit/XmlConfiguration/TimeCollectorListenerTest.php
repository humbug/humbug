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

use Humbug\Adapter\Phpunit\XmlConfiguration\TimeCollectorListener;

class TimeCollectorListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TimeCollectorListener
     */
    private $timeCollectorListener;

    private $pathToTimesHumbugJson = '/path/to/phpunit.times.humbug.json';

    protected function setUp()
    {
        $this->timeCollectorListener = new TimeCollectorListener($this->pathToTimesHumbugJson);
    }

    public function testShouldBeVisitor()
    {
        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor', $this->timeCollectorListener);
    }

    public function testShouldUpdateDomElement()
    {
        $dom = new \DOMDocument();
        $domElement = $dom->createElement('listener');
        $dom->appendChild($domElement);

        $this->timeCollectorListener->visitElement($domElement);

        $this->assertEquals('\Humbug\Phpunit\Listener\TimeCollectorListener', $domElement->getAttribute('class'));

        $xpath = new \DOMXPath($dom);

        $this->assertEquals(1, $xpath->evaluate('count(/listener/arguments)'));
        $this->assertEquals(1, $xpath->evaluate('count(/listener/arguments/object)'));

        $loggerObject = $xpath->query('/listener/arguments/object')->item(0);

        $this->assertInstanceOf('\DOMElement', $loggerObject);
        $this->assertEquals('\Humbug\Phpunit\Logger\JsonLogger', $loggerObject->getAttribute('class'));

        $this->assertEquals(1, $xpath->evaluate('count(/listener/arguments/object/arguments)'));
        $this->assertEquals(1, $xpath->evaluate('count(/listener/arguments/object/arguments/string)'));

        $actualPathToHumbugJson = $xpath->query('/listener/arguments/object/arguments/string')->item(0)->nodeValue;
        $this->assertEquals($this->pathToTimesHumbugJson, $actualPathToHumbugJson);
    }
}
