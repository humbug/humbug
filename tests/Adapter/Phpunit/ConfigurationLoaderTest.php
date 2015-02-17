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

namespace Humbug\Test\Adapter\Phpunit;

use Humbug\Adapter\Phpunit\ConfigurationLoader;

class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldLoadDomDocument()
    {
        $configFile = __DIR__ . '/../_files/phpunit.xml.dist';

        $loader = new ConfigurationLoader();

        $domDocument = $loader->load($configFile);

        $this->assertInstanceOf('\DomDocument', $domDocument);
        $this->assertEquals(false, $domDocument->preserveWhiteSpace);
        $this->assertEquals(true, $domDocument->formatOutput);

        $this->assertXmlStringEqualsXmlFile($configFile, $domDocument->saveXML());
    }
} 