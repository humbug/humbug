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
use Humbug\Adapter\Phpunit\ConfigurationLoader;
use Humbug\Adapter\Phpunit\XmlConfiguration;

class XmlConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldThrowExceptionIfNoDocumentElementIsPresent()
    {
        $this->setExpectedException('\LogicException', 'No document element present. Document should not be empty!');

        $dom = new \DOMDocument();
        new XmlConfiguration($dom);
    }

    public function testShouldHaveBootstrap()
    {
        $dom = $this->createDomWithBootstrap();

        $xmlConfiguration = new XmlConfiguration($dom);

        $this->assertTrue($xmlConfiguration->hasBootstrap());
        $this->assertEquals('/test/bootstrap.php', $xmlConfiguration->getBootstrap());
    }

    public function testShouldHaveOriginalBootstrap()
    {
        $dom = $this->createDomWithBootstrap();

        $xmlConfiguration = new XmlConfiguration($dom);

        $this->assertTrue($xmlConfiguration->hasOriginalBootstrap());
        $this->assertEquals('/test/bootstrap.php', $xmlConfiguration->getOriginalBootstrap());

        $xmlConfiguration->setBootstrap('/new/test/bootstrap.php');

        $this->assertEquals('/test/bootstrap.php', $xmlConfiguration->getOriginalBootstrap());

        $xmlConfiguration->setBootstrap('/newest/test/bootstrap.php');

        $this->assertEquals('/test/bootstrap.php', $xmlConfiguration->getOriginalBootstrap());
    }

    public function testShouldHaveOriginalEmptyBootstrap()
    {
        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $this->assertFalse($xmlConfiguration->hasOriginalBootstrap());
        $this->assertNull($xmlConfiguration->getOriginalBootstrap());

        $xmlConfiguration->setBootstrap('/new/bootstrap');

        $this->assertNull($xmlConfiguration->getOriginalBootstrap());
    }

    public function testShouldUpdateBootstrap()
    {
        $dom = $this->createDomWithBootstrap();

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->setBootstrap('/test/new/bootstrap.php');

        $this->assertEquals('/test/new/bootstrap.php', $xmlConfiguration->getBootstrap());
    }

    /**
     * @return \DOMDocument
     */
    private function createDomWithBootstrap()
    {
        $dom = $this->createBaseDomDocument();

        $dom->documentElement->setAttribute('bootstrap', '/test/bootstrap.php');

        return $dom;
    }

    /**
     * @return \DOMDocument
     */
    private function createBaseDomDocument()
    {
        $dom = new \DOMDocument();

        $dom->appendChild($dom->createElement('phpunit'));

        return $dom;
    }

    public function testShouldTurnOffCacheTokens()
    {
        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->turnOffCacheTokens();

        $this->assertEquals('false', $dom->documentElement->getAttribute('cacheTokens'));
    }

    public function testShouldCleanupLoggers()
    {
        $dom = $this->createDocumentWithChildElement('logging');

        (new XmlConfiguration($dom))->cleanupLoggers();

        $this->assertThatDomNodeIsNotPresent($dom, 'logging');
    }

    public function testShouldCleanupFilters()
    {
        $dom = $this->createDocumentWithChildElement('filter');

        (new XmlConfiguration($dom))->cleanupFilters();

        $this->assertThatDomNodeIsNotPresent($dom, 'filter');
    }

    public function testShouldCleanupListeners()
    {
        $dom = $this->createDocumentWithChildElement('listeners');

        (new XmlConfiguration($dom))->cleanupListeners();

        $this->assertThatDomNodeIsNotPresent($dom, 'listeners');
    }

    /**
     * @param $childElement
     * @return \DOMDocument
     */
    private function createDocumentWithChildElement($childElement)
    {
        $dom = $this->createBaseDomDocument();

        $dom->documentElement->appendChild($dom->createElement($childElement));

        return $dom;
    }

    private function assertThatDomNodeIsNotPresent($dom, $nodeName)
    {
        $this->assertEquals(0, (new \DOMXPath($dom))->evaluate('count(/phpunit/' . $nodeName . ')'));
    }

    public function testShouldAddListener()
    {
        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $visitor = $this->createMock('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor');

        $visitor->expects($this->once())->method('visitElement')->with($this->isInstanceOf('\DOMElement'));

        $xmlConfiguration->addListener($visitor);

        $listeners = (new \DOMXPath($dom))->query('/phpunit/listeners/listener');

        $this->assertEquals(1, $listeners->length);
    }

    public function testShouldAddListeners()
    {
        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $visitor = $this->createMock('Humbug\Adapter\Phpunit\XmlConfiguration\Visitor');

        $visitor->expects($this->exactly(2))->method('visitElement')->with($this->isInstanceOf('\DOMElement'));

        $xmlConfiguration->addListener($visitor);
        $xmlConfiguration->addListener($visitor);

        $xpath = (new \DOMXPath($dom));

        $this->assertEquals(1, $xpath->query('/phpunit/listeners')->length);
        $this->assertEquals(2, $xpath->query('/phpunit/listeners/listener')->length);
    }

    public function testShouldAddLogger()
    {
        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->addLogger('logger-type', '/path/to/target');

        $xpath = (new \DOMXPath($dom));

        $this->assertEquals(1, $xpath->evaluate('count(/phpunit/logging)'));

        $logList = $xpath->query('/phpunit/logging/log');
        $this->assertEquals(1, $logList->length);

        $log = $logList->item(0);

        $this->assertEquals('logger-type', $log->getAttribute('type'));
        $this->assertEquals('/path/to/target', $log->getAttribute('target'));

        //second logger
        $xmlConfiguration->addLogger('type-2', '/target2');

        $xpath = (new \DOMXPath($dom));

        $this->assertEquals(1, $xpath->evaluate('count(/phpunit/logging)'));

        $logList = $xpath->query('/phpunit/logging/log');
        $this->assertEquals(2, $logList->length);

        $log = $logList->item(1);

        $this->assertEquals('type-2', $log->getAttribute('type'));
        $this->assertEquals('/target2', $log->getAttribute('target'));
    }

    public function testShouldAddWhiteListDirsFilter()
    {
        $whiteList = [
            '/src/lib',
            '/src/common'
        ];

        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->addWhiteListFilter($whiteList);

        $xpath = new \DOMXPath($dom);

        $this->assertEquals(1, $xpath->evaluate('count(/phpunit/filter)'));
        $this->assertEquals(1, $xpath->evaluate('count(/phpunit/filter/whitelist)'));
        $this->assertEquals(0, $xpath->evaluate('count(/phpunit/filter/whitelist/exclude)'));

        $actualDirList = $xpath->query('/phpunit/filter/whitelist/directory');

        $this->assertEquals(2, $actualDirList->length);

        $dir1 = $actualDirList->item(0);
        $this->assertEquals('/src/lib', $dir1->nodeValue);
        $this->assertEquals('.php', $dir1->getAttribute('suffix'));

        $dir2 = $actualDirList->item(1);
        $this->assertEquals('/src/common', $dir2->nodeValue);
        $this->assertEquals('.php', $dir2->getAttribute('suffix'));
    }

    public function testShouldNotAddWhiteListDirsFilter()
    {
        $whiteList = [];

        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->addWhiteListFilter($whiteList);

        $this->assertEquals(0, (new \DOMXPath($dom))->evaluate('count(/phpunit/filter)'));
    }

    public function testShouldAddWhiteListDirsWithExcludes()
    {
        $whiteList = [
            '/src',
        ];

        $excludeDirs = [
            '/src/covers-nothing',
            '/src/excluded',
        ];

        $dom = $this->createBaseDomDocument();

        $xmlConfiguration = new XmlConfiguration($dom);

        $xmlConfiguration->addWhiteListFilter($whiteList, $excludeDirs);

        $xpath = new \DOMXPath($dom);

        $this->assertEquals(1, $xpath->evaluate('count(/phpunit/filter/whitelist/directory)'));
        $this->assertEquals(1, $xpath->evaluate('count(/phpunit/filter/whitelist/exclude)'));

        $excludedList = $xpath->query('/phpunit/filter/whitelist/exclude/directory');

        $this->assertEquals(2, $excludedList->length);

        $this->assertEquals('/src/covers-nothing', $excludedList->item(0)->nodeValue);
        $this->assertEquals('/src/excluded', $excludedList->item(1)->nodeValue);
    }

    public function testShouldReplaceDirectoryPathsToAbsolutePathsInWholeDocument()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');
        $dom = (new ConfigurationLoader())->load($configurationDir . '/phpunit.xml');

        $xmlConfiguration = new XmlConfiguration($dom);

        $locator = new Locator($configurationDir);
        $replacePathVisitor = new XmlConfiguration\ReplacePathVisitor($locator);
        $replaceWildCardVisitor = new XmlConfiguration\ReplaceWildcardVisitor($locator);

        $xmlConfiguration->replacePathsToAbsolutePaths($replacePathVisitor, $replaceWildCardVisitor);

        $xpath = new \DOMXPath($dom);

        $actualSuiteDirectory = $xpath->query('/phpunit/testsuites/testsuite/directory')->item(0)->nodeValue;
        $this->assertEquals($configurationDir, $actualSuiteDirectory);

        $actualWhiteListDirectory = $xpath->query('/phpunit/filter/whitelist/directory')->item(0)->nodeValue;
        $this->assertEquals($configurationDir . DIRECTORY_SEPARATOR . 'white-list', $actualWhiteListDirectory);

        $actualWhiteListExcludeDirectory =
            $xpath->query('/phpunit/filter/whitelist/exclude/directory')->item(0)->nodeValue;
        $this->assertEquals($configurationDir . DIRECTORY_SEPARATOR . 'white-list' . DIRECTORY_SEPARATOR . 'exclude', $actualWhiteListExcludeDirectory);
    }

    public function testShouldReplaceFilePathsToAbsolutePaths()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');
        $dom = (new ConfigurationLoader())->load($configurationDir . '/phpunit.xml');

        $xmlConfiguration = new XmlConfiguration($dom);

        $locator = new Locator($configurationDir);
        $replacePathVisitor = new XmlConfiguration\ReplacePathVisitor($locator);
        $replaceWildCardVisitor = new XmlConfiguration\ReplaceWildcardVisitor($locator);

        $xmlConfiguration->replacePathsToAbsolutePaths($replacePathVisitor, $replaceWildCardVisitor);

        $xpath = new \DOMXPath($dom);

        $actualSuiteDirectory = $xpath->query('/phpunit/testsuites/testsuite/file')->item(0)->nodeValue;
        $this->assertEquals($configurationDir . DIRECTORY_SEPARATOR . 'file.php', $actualSuiteDirectory);
    }

    public function testShouldReplaceSuiteExcludesWithAbsolutePaths()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');
        $dom = (new ConfigurationLoader())->load($configurationDir . '/phpunit.xml');

        $xmlConfiguration = new XmlConfiguration($dom);

        $locator = new Locator($configurationDir);
        $replacePathVisitor = new XmlConfiguration\ReplacePathVisitor($locator);
        $replaceWildCardVisitor = new XmlConfiguration\ReplaceWildcardVisitor($locator);

        $xmlConfiguration->replacePathsToAbsolutePaths($replacePathVisitor, $replaceWildCardVisitor);

        $xpath = new \DOMXPath($dom);

        $actualSuiteExclude = $xpath->query('/phpunit/testsuites/testsuite/exclude')->item(0)->nodeValue;
        $this->assertEquals($configurationDir . DIRECTORY_SEPARATOR . 'excluded-tests', $actualSuiteExclude);
    }

    public function testShouldReplaceBootstrapWithAbsolutePath()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');
        $dom = (new ConfigurationLoader())->load($configurationDir . '/phpunit.xml');

        $xmlConfiguration = new XmlConfiguration($dom);

        $locator = new Locator($configurationDir);
        $replacePathVisitor = new XmlConfiguration\ReplacePathVisitor($locator);
        $replaceWildCardVisitor = new XmlConfiguration\ReplaceWildcardVisitor($locator);

        $xmlConfiguration->replacePathsToAbsolutePaths($replacePathVisitor, $replaceWildCardVisitor);

        $xpath = new \DOMXPath($dom);

        $actualBootstrapPath = $xpath->query('/phpunit/@bootstrap')->item(0)->nodeValue;
        $this->assertEquals($configurationDir . DIRECTORY_SEPARATOR . 'file.php', $actualBootstrapPath);
    }

    public function testShouldReplaceWildcardsWithAbsolutePaths()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/regression/wildcard-dirs');
        $dom = (new ConfigurationLoader())->load($configurationDir . '/phpunit.xml');

        $xmlConfiguration = new XmlConfiguration($dom);

        $locator = new Locator($configurationDir);

        $replacePathVisitor = new XmlConfiguration\ReplacePathVisitor($locator);
        $replaceWildCardVisitor = new XmlConfiguration\ReplaceWildcardVisitor($locator);

        $xmlConfiguration->replacePathsToAbsolutePaths($replacePathVisitor, $replaceWildCardVisitor);

        $xpath = new \DOMXPath($dom);

        $firstSuitePath = realpath($configurationDir . '/suite-first/Tests');
        $firstReplaced = $xpath->query('/phpunit/testsuites/testsuite[@name="Test Suite"]/directory[text()="' . $firstSuitePath . '"]');

        $this->assertEquals(1, $firstReplaced->length);

        $secondSuitePath = realpath($configurationDir . '/second-suite/Tests');
        $secondReplaced = $xpath->query('/phpunit/testsuites/testsuite[@name="Test Suite"]/directory[text()="' . $secondSuitePath . '"]');

        $this->assertEquals(1, $secondReplaced->length);
    }

    public function testShouldNotReplaceBootstrapWithAbsolutePath()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');
        $dom = (new ConfigurationLoader())->load($configurationDir . '/phpunit.xml');
        $xpath = new \DOMXPath($dom);

        $xpath->query('/phpunit')->item(0)->removeAttribute('bootstrap');

        $xmlConfiguration = new XmlConfiguration($dom);

        $locator = new Locator($configurationDir);
        $replacePathVisitor = new XmlConfiguration\ReplacePathVisitor($locator);
        $replaceWildCardVisitor = new XmlConfiguration\ReplaceWildcardVisitor($locator);

        $xmlConfiguration->replacePathsToAbsolutePaths($replacePathVisitor, $replaceWildCardVisitor);

        $actualBootstrapPath = $xpath->query('/phpunit/@bootstrap');
        $this->assertEquals(0, $actualBootstrapPath->length);
    }

    public function testShouldGenerateXml()
    {
        $configurationDir = realpath(__DIR__ . '/../_files/phpunit-conf');
        $dom = (new ConfigurationLoader())->load($configurationDir . '/phpunit.xml');

        $xmlConfiguration = new XmlConfiguration($dom);

        $this->assertXmlStringEqualsXmlFile($configurationDir . '/phpunit.xml', $xmlConfiguration->generateXML());
    }
}
