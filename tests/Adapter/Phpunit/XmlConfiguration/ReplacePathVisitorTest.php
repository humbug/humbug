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
use Humbug\Adapter\Phpunit\XmlConfiguration\ReplacePathVisitor;

class ReplacePathVisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReplacePathVisitor
     */
    private $replacePathVisitor;

    protected function setUp()
    {
        $locator = new Locator(__DIR__ . '/../../_files/phpunit-conf');

        $this->replacePathVisitor = new ReplacePathVisitor($locator);
    }

    public function testShouldBeVisitor()
    {
        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor', $this->replacePathVisitor);
    }

    public function testShouldReplaceValueWithRealPath()
    {
        $dom = new \DOMDocument();
        $visitedObject = $dom->createElement('object', 'file.php');
        $dom->appendChild($visitedObject);

        $this->replacePathVisitor->visitNode($visitedObject);

        $expectedPath = realpath(__DIR__ . '/../../_files/phpunit-conf/file.php');
        $this->assertEquals($expectedPath, $visitedObject->nodeValue);
    }
}
