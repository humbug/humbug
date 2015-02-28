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

use Humbug\Adapter\Locator;
use Humbug\Adapter\Phpunit\XmlConfiguration\ReplaceWildcardVisitor;

class ReplaceWildcardVisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReplaceWildcardVisitor
     */
    private $replaceWildcardVisitor;

    /**
     * @var string
     */
    private $wildcardDir;

    protected function setUp()
    {
        $this->wildcardDir = __DIR__ . '/../../_files/regression/wildcard-dirs';

        $this->replaceWildcardVisitor = new ReplaceWildcardVisitor(new Locator($this->wildcardDir));
    }

    public function testShouldBeVisitor()
    {
        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor', $this->replaceWildcardVisitor);
    }

    public function testShouldReplaceWildcardWithOneRealPath()
    {
        $dom = new \DOMDocument();
        $visitedObject = $dom->createElement('object', '*suite');
        $dom->appendChild($visitedObject);

        $this->replaceWildcardVisitor->visitElement($visitedObject);

        $xpath = new \DOMXPath($dom);

        $expected = realpath($this->wildcardDir . '/second-suite');

        $this->assertEquals($expected, $xpath->query('/object')->item(0)->nodeValue);
    }

    public function testShouldReplaceWildcardWithManyRealPaths()
    {
        $dom = new \DOMDocument();
        $visitedObject = $dom->createElement('object', '*/Tests');
        $dom->appendChild($visitedObject);

        $this->replaceWildcardVisitor->visitElement($visitedObject);
        $xpath = new \DOMXPath($dom);

        $secondExpected = realpath($this->wildcardDir . '/second-suite/Tests');
        $firstExpected = realpath($this->wildcardDir . '/suite-first/Tests');

        $this->assertEquals(1, $xpath->query('/object[text()="' . $secondExpected .'"]')->length);
        $this->assertEquals(1, $xpath->query('/object[text()="' . $firstExpected .'"]')->length);
    }
}
