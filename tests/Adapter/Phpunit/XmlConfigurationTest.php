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

use Humbug\Adapter\Phpunit\XmlConfiguration;

class XmlConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldLocateConfiguration()
    {
        $directory = __DIR__ . '/../_files/phpunit-conf';

        $configurationFile = (new XmlConfiguration($directory))->findConfigurationFile();

        $expectedXmlPath = realpath($directory . '/phpunit.xml');

        $this->assertEquals($expectedXmlPath, $configurationFile);
    }

    public function testShouldLocateDistConfiguration()
    {
        $directory = __DIR__ . '/../_files/phpunit';

        $configurationFile = (new XmlConfiguration($directory))->findConfigurationFile();

        $expectedXmlPath = realpath($directory . '/phpunit.xml.dist');

        $this->assertEquals($expectedXmlPath, $configurationFile);
    }

    public function testShouldRiseExceptionWhileLocatingConfiguration()
    {
        $directory = __DIR__;

        $this->setExpectedException('\Humbug\Exception\RuntimeException');

        (new XmlConfiguration($directory))->findConfigurationFile();
    }

    public function testShouldHaveConfigurationDir()
    {
        $configurationDir = 'path/to/configuration';

        $xmlConfiguration = new XmlConfiguration($configurationDir);

        $this->assertEquals($configurationDir, $xmlConfiguration->getConfigurationDir());
    }
} 