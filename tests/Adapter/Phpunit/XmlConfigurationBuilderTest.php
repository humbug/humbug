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

use Humbug\Adapter\Phpunit\XmlConfigurationBuilder;

class XmlConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBuildXmlConfigurationFromConfigurationDirectory()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');

        $builder = new XmlConfigurationBuilder();

        $xmlConfiguration = $builder->build($configurationDir);

        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration', $xmlConfiguration);

        $this->assertEquals(sys_get_temp_dir() . '/humbug.phpunit.bootstrap.php', $xmlConfiguration->getBootstrap());

        $dom = (new \DOMDocument());
        $dom->loadXML($xmlConfiguration->generateXML());

        $xpath = new \DOMXPath($dom);

        $cacheTokens = $xpath->query('/phpunit/@cacheTokens');

        $this->assertEquals('false', $cacheTokens->item(0)->nodeValue);

    }
}