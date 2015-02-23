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
    /**
     * @var XmlConfigurationBuilder
     */
    private $builder;

    protected function setUp()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');

        $this->builder = new XmlConfigurationBuilder($configurationDir);
    }

    public function testShouldBuildXmlConfigurationFromConfigurationDirectory()
    {
        $xmlConfiguration = $this->builder->build();

        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration', $xmlConfiguration);

        $this->assertEquals(sys_get_temp_dir() . '/humbug.phpunit.bootstrap.php', $xmlConfiguration->getBootstrap());

        $xpath = $this->createXpathFromXmlConfiguration($xmlConfiguration);

        $cacheTokens = $xpath->query('/phpunit/@cacheTokens');

        $this->assertEquals('false', $cacheTokens->item(0)->nodeValue);
        $this->assertEquals(0, $xpath->evaluate('count(/phpunit/logging|/phpunit/filter|/phpunit/listeners)'));
    }

    public function testShouldBuildConfigurationWithPhpCoverage()
    {
        $this->builder->setPhpCoverage('file/coverage.php');

        $xmlConfiguration = $this->builder->build();

        $xpath = $this->createXpathFromXmlConfiguration($xmlConfiguration);

        $logList = $xpath->query('/phpunit/logging/log');

        $this->assertEquals(1, $logList->length);

        $log = $logList->item(0);
        $this->assertEquals('coverage-php', $log->getAttribute('type'));
        $this->assertEquals('file/coverage.php', $log->getAttribute('target'));
    }

    public function testShouldBuildConfigurationWithTextCoverage()
    {
        $this->builder->setTextCoverage('file/coverage.txt');

        $xmlConfiguration = $this->builder->build();

        $xpath = $this->createXpathFromXmlCOnfiguration($xmlConfiguration);

        $logList = $xpath->query('/phpunit/logging/log');

        $this->assertEquals(1, $logList->length);

        $log = $logList->item(0);
        $this->assertEquals('coverage-text', $log->getAttribute('type'));
        $this->assertEquals('file/coverage.txt', $log->getAttribute('target'));
    }

    public function testShouldBuildConfigurationWithTimeCollectorListener()
    {
        $this->builder->setTimeCollectionListener('path/to/stats.json');

        $xmlConfiguration = $this->builder->build();

        $xpath = $this->createXpathFromXmlConfiguration($xmlConfiguration);

        $timeCollectionListenerList = $xpath->query('/phpunit/listeners/listener');

        $this->assertEquals(1, $timeCollectionListenerList->length);
        $timeCollectionListener = $timeCollectionListenerList->item(0);

        $this->assertEquals('\Humbug\Phpunit\Listener\TimeCollectorListener', $timeCollectionListener->getAttribute('class'));

        $jsonLogger = $xpath->query('/phpunit/listeners/listener/arguments/object')->item(0);
        $this->assertEquals('\Humbug\Phpunit\Logger\JsonLogger', $jsonLogger->getAttribute('class'));

        $jsonLoggerArgument = $xpath->query('/phpunit/listeners/listener/arguments/object/arguments/string')->item(0);
        $this->assertEquals('path/to/stats.json', $jsonLoggerArgument->nodeValue);
    }

    public function testShouldBuildConfigurationWithFilterListener()
    {
        $testSuites = [
            'suite'
        ];

        $this->builder->setFilterListener($testSuites, 'path/to/stats.json');

        $xmlConfiguration = $this->builder->build();

        $xpath = $this->createXpathFromXmlConfiguration($xmlConfiguration);

        $filterListenerList = $xpath->query('/phpunit/listeners/listener');
        $this->assertEquals(1, $filterListenerList->length);

        $filterListener = $filterListenerList->item(0);
        $this->assertEquals('\Humbug\Phpunit\Listener\FilterListener', $filterListener->getAttribute('class'));

        $filtersList = $xpath->query('/phpunit/listeners/listener/arguments/object');
        $this->assertEquals(2, $filtersList->length);

        $firstFilter = $filtersList->item(0);
        $this->assertEquals('\Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter', $firstFilter->getAttribute('class'));

        $actualSuite = $xpath->query('/phpunit/listeners/listener/arguments/object[position()=1]/arguments/string');
        $this->assertEquals('suite', $actualSuite->item(0)->nodeValue);

        $secondFilter = $filtersList->item(1);
        $this->assertEquals('\Humbug\Phpunit\Filter\TestSuite\FastestFirstFilter', $secondFilter->getAttribute('class'));

        $actualFilterStats = $xpath->query('/phpunit/listeners/listener/arguments/object[position()=2]/arguments/string');
        $this->assertEquals('path/to/stats.json', $actualFilterStats->item(0)->nodeValue);
    }

    /**
     * @param $xmlConfiguration
     * @return \DOMXPath
     */
    protected function createXpathFromXmlConfiguration($xmlConfiguration)
    {
        $dom = (new \DOMDocument());
        $dom->loadXML($xmlConfiguration->generateXML());

        $xpath = new \DOMXPath($dom);
        return $xpath;
    }
}