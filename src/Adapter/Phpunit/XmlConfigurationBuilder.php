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

namespace Humbug\Adapter\Phpunit;

use Humbug\Adapter\Locator;
use Humbug\Adapter\Phpunit\XmlConfiguration\ObjectVisitor;
use Humbug\Adapter\Phpunit\XmlConfiguration\ReplacePathVisitor;
use Humbug\Adapter\Phpunit\XmlConfiguration\ReplaceWildcardVisitor;

class XmlConfigurationBuilder
{
    protected $xmlConfigurationClass = '\Humbug\Adapter\Phpunit\XmlConfiguration';

    /**
     * @var string
     */
    private $configurationDir;

    /**
     * @var string
     */
    private $phpCoveragePath;

    /**
     * @var string
     */
    private $textCoveragePath;

    /**
     * @var string
     */
    private $junitLogPath;

    /**
     * @var string
     */
    private $pathToTimeStats;

    /**
     * @var array
     */
    private $filterTestSuites = [];

    /**
     * @var string
     */
    private $filterStatsPath;

    /**
     * @var array
     */
    private $coverageWhiteListDirs;

    /**
     * @var array
     */
    private $coverageExcludeDirs;

    /**
     * @var bool
     */
    private $acceleratorListener = false;

    /**
     * @var int
     */
    private $testSuiteNestingLevel = 0;

    public function __construct($configurationDir)
    {
        $this->configurationDir = $configurationDir;
    }

    public function getConfiguration()
    {
        $xmlConfiguration = $this->createXmlConfiguration();

        $this->initializeConfiguration($xmlConfiguration);

        if ($this->phpCoveragePath) {
            $xmlConfiguration->addLogger('coverage-php', $this->phpCoveragePath);
        }

        if ($this->textCoveragePath) {
            $xmlConfiguration->addLogger('coverage-text', $this->textCoveragePath);
        }

        if ($this->junitLogPath) {
            $xmlConfiguration->addLogger('junit', $this->junitLogPath);
        }

        if (!empty($this->coverageWhiteListDirs) || !empty($this->coverageExcludeDirs)) {
            $xmlConfiguration->addWhiteListFilter($this->coverageWhiteListDirs, $this->coverageExcludeDirs);
        }

        if ($this->pathToTimeStats) {
            $timeCollectionListener = new ObjectVisitor('\Humbug\Adapter\Phpunit\Listeners\JsonLoggingTimeCollectorListener', [
                $this->pathToTimeStats,
                $xmlConfiguration->getRootTestSuiteNestingLevel()
            ]);
            $xmlConfiguration->addListener($timeCollectionListener);
        }

        if (!empty($this->filterTestSuites) || $this->filterStatsPath) {
            $filterListener = new ObjectVisitor('\Humbug\Adapter\Phpunit\Listeners\TestSuiteFilterListener', array_merge([
                $xmlConfiguration->getRootTestSuiteNestingLevel(),
                $this->filterStatsPath
            ], $this->filterTestSuites));
            $xmlConfiguration->addListener($filterListener);
        }

        /**
         * Disable deprecation notices for Symfony
         */
        $xmlConfiguration->addEnvironmentVariable('SYMFONY_DEPRECATIONS_HELPER', 'weak');

        return $xmlConfiguration;
    }

    private function getNewBootstrapPath()
    {
        return sys_get_temp_dir() . '/humbug.phpunit.bootstrap.php';
    }

    /**
     * @param XmlConfiguration $xmlConfiguration
     * @return XmlConfiguration
     */
    private function initializeConfiguration(XmlConfiguration $xmlConfiguration)
    {
        $locator = new Locator($this->configurationDir);

        $replacePathVisitor = new ReplacePathVisitor($locator);
        $replaceWildcardVisitor = new ReplaceWildcardVisitor($locator);
        $xmlConfiguration->replacePathsToAbsolutePaths($replacePathVisitor, $replaceWildcardVisitor);

        $xmlConfiguration->setBootstrap($this->getNewBootstrapPath());
        $xmlConfiguration->turnOffCacheTokens();

        $xmlConfiguration->cleanupFilters();
        $xmlConfiguration->cleanupLoggers();
        $xmlConfiguration->cleanupListeners();
    }

    /**
     * @return XmlConfiguration
     */
    protected function createXmlConfiguration()
    {
        $configurationFile = (new ConfigurationLocator())->locate($this->configurationDir);

        $dom = (new ConfigurationLoader())->load($configurationFile);

        return new $this->xmlConfigurationClass($dom);
    }

    public function setAcceleratorListener()
    {
        $this->acceleratorListener = true;
    }

    public function setPhpCoverage($phpCoveragePath)
    {
        $this->phpCoveragePath = $phpCoveragePath;
    }

    public function setTextCoverage($textCoveragePath)
    {
        $this->textCoveragePath = $textCoveragePath;
    }

    public function setJunitLog($junitLogPath)
    {
        $this->junitLogPath = $junitLogPath;
    }

    public function setTimeCollectionListener($pathToTimeStats)
    {
        $this->pathToTimeStats = $pathToTimeStats;
    }

    public function setFilterListener(array $testSuites, $filterStatsPath)
    {
        $this->filterTestSuites = $testSuites;
        $this->filterStatsPath = $filterStatsPath;
    }

    public function setCoverageFilter(array $coverageWhiteListDirs, array $coverageExcludeDirs)
    {
        $this->coverageWhiteListDirs = $coverageWhiteListDirs;
        $this->coverageExcludeDirs = $coverageExcludeDirs;
    }

    /**
     * @param int $level
     */
    public function setTestSuiteNestingLevel($level)
    {
        $this->testSuiteNestingLevel = $level;
    }
}
