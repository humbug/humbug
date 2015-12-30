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

use Humbug\Adapter\Locator;
use Humbug\Adapter\Phpunit\XmlConfiguration;
use Humbug\Adapter\Phpunit\XmlConfiguration\ObjectVisitor;
use Humbug\Adapter\Phpunit\XmlConfigurationBuilder;

class XmlConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FakeConfigurationBuilder
     */
    private $builder;

    private $configurationDir;

    protected function setUp()
    {
        $this->configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');

        $this->builder = new FakeConfigurationBuilder($this->configurationDir);
    }

    public function testShouldBuildXmlConfigurationFromConfigurationDirectory()
    {
        $xmlConfiguration = $this->builder->getConfiguration();

        $this->assertInstanceOf('Humbug\Adapter\Phpunit\XmlConfiguration', $xmlConfiguration);

        $this->assertEquals(sys_get_temp_dir() . '/humbug.phpunit.bootstrap.php', $xmlConfiguration->getBootstrap());

        $dom = (new \DOMDocument());
        $dom->loadXML($xmlConfiguration->generateXML());

        $xpath = new \DOMXPath($dom);

        $cacheTokens = $xpath->query('/phpunit/@cacheTokens');

        $this->assertEquals('false', $cacheTokens->item(0)->nodeValue);
        $this->assertEquals(0, $xpath->evaluate('count(/phpunit/logging|/phpunit/filter|/phpunit/listeners)'));

        $expectedPathVisitor = new XmlConfiguration\ReplacePathVisitor(new Locator($this->configurationDir));
        $expectedWildcardVisitor = new XmlConfiguration\ReplaceWildcardVisitor(new Locator($this->configurationDir));

        $xmlConfiguration->wasCalledWith('replacePathsToAbsolutePaths', [$expectedPathVisitor, $expectedWildcardVisitor], 0);
    }

    public function testShouldBuildConfigurationWithAcceleratorListener()
    {
        $this->builder->setAcceleratorListener();

        $xmlConfiguration = $this->builder->getConfiguration();

        $acceleratorListener = new ObjectVisitor('\MyBuilder\PhpunitAccelerator\TestListener', [true]);

        $xmlConfiguration->wasCalledWith('addListener', [$acceleratorListener]);
    }

    public function testShouldBuildConfigurationWithPhpCoverage()
    {
        $this->builder->setPhpCoverage('file/coverage.php');

        $xmlConfiguration = $this->builder->getConfiguration();

        $xmlConfiguration->wasCalledWith('addLogger', ['coverage-php', 'file/coverage.php']);
    }

    public function testShouldBuildConfigurationWithTextCoverage()
    {
        $this->builder->setTextCoverage('file/coverage.txt');

        $xmlConfiguration = $this->builder->getConfiguration();

        $xmlConfiguration->wasCalledWith('addLogger', ['coverage-text', 'file/coverage.txt']);
    }

    public function testShouldBuildConfigurationWithTimeCollectorListener()
    {
        $this->builder->setTimeCollectionListener('path/to/stats.json');

        $xmlConfiguration = $this->builder->getConfiguration();

        $timeCollectionListener = new ObjectVisitor(
            '\Humbug\Adapter\Phpunit\Listeners\JsonLoggingTimeCollectorListener',
            [
                'path/to/stats.json',
                0 //root suite nesting level
            ]
        );

        $xmlConfiguration->wasCalledWith('addListener', [$timeCollectionListener]);
    }

    public function testShouldBuildConfigurationWithFilterListener()
    {
        $testSuites = [
            'suite'
        ];

        $this->builder->setFilterListener($testSuites, 'path/to/stats.json');

        $xmlConfiguration = $this->builder->getConfiguration();

        $filterListener = new ObjectVisitor('\Humbug\Phpunit\Listener\FilterListener', [
            0, //root suite nesting level
            new ObjectVisitor('\Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter', $testSuites),
            new ObjectVisitor('\Humbug\Phpunit\Filter\TestSuite\FastestFirstFilter', ['path/to/stats.json'])
        ]);

        $xmlConfiguration->wasCalledWith('addListener', [$filterListener]);
    }

    public function testShouldBuildConfigurationWithCoverageFilter()
    {
        $whiteList = [
            'dir1',
            'dir2'
        ];

        $exclude = [
            'ex1',
            'ex2'
        ];

        $this->builder->setCoverageFilter($whiteList, $exclude);

        $xmlConfiguration = $this->builder->getConfiguration();

        $xmlConfiguration->wasCalledWith('addWhiteListFilter', [$whiteList, $exclude]);
    }
}

/**
 * @method FakeConfiguration getConfiguration
 */
class FakeConfigurationBuilder extends XmlConfigurationBuilder
{
    protected $xmlConfigurationClass = '\Humbug\Test\Adapter\Phpunit\FakeConfiguration';
}

class FakeConfiguration extends XmlConfiguration
{
    private $calls = [];

    public function addLogger($type, $target)
    {
        $this->calls[][__FUNCTION__] = func_get_args();
    }

    public function addListener(XmlConfiguration\Visitor $visitor)
    {
        $this->calls[][__FUNCTION__] = func_get_args();
    }

    public function addWhiteListFilter(array $whiteList, array $exclude = [])
    {
        $this->calls[][__FUNCTION__] = func_get_args();
    }

    public function replacePathsToAbsolutePaths(XmlConfiguration\Visitor $pathVisitor, XmlConfiguration\Visitor $wildcardVisitor)
    {
        $this->calls[][__FUNCTION__] = func_get_args();
    }

    public function wasCalledWith($function, $arguments, $at = 1)
    {
        \PHPUnit_Framework_Assert::assertTrue(isset($this->calls[$at][$function]));
        \PHPUnit_Framework_Assert::assertEquals($arguments, $this->calls[$at][$function]);
    }
}
