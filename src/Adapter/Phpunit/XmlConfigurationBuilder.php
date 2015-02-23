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

use Humbug\Adapter\Phpunit\XmlConfiguration\ObjectVisitor;

class XmlConfigurationBuilder
{
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

    public function __construct($configurationDir)
    {
        $this->configurationDir = $configurationDir;
    }

    public function build()
    {
        $xmlConfiguration = $this->createXmlConfiguration();

        $this->initializeConfiguration($xmlConfiguration);

        if ($this->phpCoveragePath) {
            $xmlConfiguration->addLogger('coverage-php', $this->phpCoveragePath);
        }

        if ($this->textCoveragePath) {
            $xmlConfiguration->addLogger('coverage-text', $this->textCoveragePath);
        }

        if (!empty($this->coverageWhiteListDirs) || !empty($this->coverageExcludeDirs)) {
            $xmlConfiguration->addWhiteListFilter($this->coverageWhiteListDirs, $this->coverageExcludeDirs);
        }

        if ($this->pathToTimeStats) {
            $timeCollectionListener = new ObjectVisitor('\Humbug\Phpunit\Listener\TimeCollectorListener', [
                new ObjectVisitor('\Humbug\Phpunit\Logger\JsonLogger', [$this->pathToTimeStats])
            ]);
            $xmlConfiguration->addListener($timeCollectionListener);
        }

        if (!empty($this->filterTestSuites) || $this->filterStatsPath) {
            $filterListener = new ObjectVisitor('\Humbug\Phpunit\Listener\FilterListener', [
                new ObjectVisitor('\Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter', $this->filterTestSuites),
                new ObjectVisitor('\Humbug\Phpunit\Filter\TestSuite\FastestFirstFilter', [$this->filterStatsPath])
            ]);
            $xmlConfiguration->addListener($filterListener);
        }

        $this->finalizeConfiguration($xmlConfiguration);

        return $xmlConfiguration;
    }

    private function getNewBootstrapPath()
    {
        return sys_get_temp_dir() . '/humbug.phpunit.bootstrap.php';
    }

    /**
     * @return XmlConfiguration
     */
    private function initializeConfiguration(XmlConfiguration $xmlConfiguration)
    {
        $xmlConfiguration->setBootstrap($this->getNewBootstrapPath());
        $xmlConfiguration->turnOffCacheTokens();

        $xmlConfiguration->cleanupFilters();
        $xmlConfiguration->cleanupLoggers();
        $xmlConfiguration->cleanupListeners();
    }

    /**
     * @return XmlConfiguration
     */
    private function createXmlConfiguration()
    {
        $configurationFile = (new ConfigurationLocator())->locate($this->configurationDir);

        $dom = (new ConfigurationLoader())->load($configurationFile);

        return new XmlConfiguration($dom);
    }

    private function finalizeConfiguration(XmlConfiguration $xmlConfiguration)
    {
        $xmlConfiguration->replacePathsToAbsolutePaths($this->configurationDir);
    }

    public function setPhpCoverage($phpCoveragePath)
    {
        $this->phpCoveragePath = $phpCoveragePath;
    }

    public function setTextCoverage($textCoveragePath)
    {
        $this->textCoveragePath = $textCoveragePath;
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
}
