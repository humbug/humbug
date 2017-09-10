<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test\Adapter;

use Humbug\Adapter\Phpunit;
use Mockery as m;

class PhpunitTest extends \PHPUnit\Framework\TestCase
{
    private $root;
    private $tmpDir;

    public function setUp()
    {
        $this->root = dirname(__FILE__) . '/_files';

        $tmpDir = sys_get_temp_dir() . '/' . rand(1000000, 9999999);

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir);
        }

        $this->tmpDir = $tmpDir;
    }

    public function tearDown()
    {
        if (file_exists($this->tmpDir . '/phpunit.times.humbug.json')) {
            unlink($this->tmpDir . '/phpunit.times.humbug.json');
        }

        if (file_exists($this->tmpDir . '/coverage.humbug.php')) {
            unlink($this->tmpDir . '/coverage.humbug.php');
        }

        if (file_exists($this->tmpDir . '/coverage.humbug.txt')) {
            unlink($this->tmpDir . '/coverage.humbug.txt');
        }

        if (file_exists($this->tmpDir . '/phpunit.humbug.xml')) {
            unlink($this->tmpDir . '/phpunit.humbug.xml');
        }

        if (file_exists($this->tmpDir . '/junit.humbug.xml')) {
            unlink($this->tmpDir . '/junit.humbug.xml');
        }

        if (file_exists($this->tmpDir)) {
            rmdir($this->tmpDir);
        }

        m::close();
    }

    /**
     * @group baserun
     */
    public function testAdapterRunsDefaultPhpunitCommand()
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceList'    => __DIR__ . '/_files/phpunit',
            'getTestRunDirectory'      => __DIR__ . '/_files/phpunit',
            'getBaseDirectory'      => __DIR__ . '/_files/phpunit',
            'getTimeout'            => 1200,
            'getTempDirectory'     => $this->tmpDir,
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'MM1_MathTest MathTest.php'
        ]);

        $adapter = new Phpunit;
        $process = $adapter->getProcess(
            $container,
            true,
            true
        );
        $process->run();

        $result = $process->getOutput();

        $this->assertContains('##teamcity[', $result, $process->getErrorOutput());
        $this->assertTrue($adapter->ok($result));
    }

    public function testAdapterDetectsTestsPassing()
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceList'    => $this->root,
            'getTestRunDirectory'      => $this->root,
            'getBaseDirectory'      => $this->root,
            'getTimeout'            => 1200,
            'getTempDirectory'     => $this->tmpDir,
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'PassTest'
        ]);

        $adapter = new Phpunit;
        $process = $adapter->getProcess(
            $container,
            true,
            true
        );
        $process->run();

        $result = $process->getOutput();

        $this->assertTrue($adapter->ok($result), $process->getErrorOutput());
    }

    public function testAdapterDetectsTestsFailingFromTestFail()
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceList'    => $this->root,
            'getTestRunDirectory'      => $this->root,
            'getBaseDirectory'      => $this->root,
            'getTimeout'            => 1200,
            'getTempDirectory'     => $this->tmpDir,
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'FailTest'
        ]);

        $adapter = new Phpunit;
        $process = $adapter->getProcess(
            $container,
            true,
            true
        );
        $process->run();

        $result = $process->getOutput();

        $this->assertContains('##teamcity[', $result);
        $this->assertFalse($adapter->ok($result), $process->getErrorOutput());
    }

    public function testAdapterDetectsTestsFailingFromException()
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceList'    => $this->root,
            'getTestRunDirectory'      => $this->root,
            'getBaseDirectory'      => $this->root,
            'getTimeout'            => 1200,
            'getTempDirectory'     => $this->tmpDir,
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'ExceptionTest'
        ]);

        $adapter = new Phpunit;
        $process = $adapter->getProcess(
            $container,
            true,
            true
        );
        $process->run();

        $result = $process->getOutput();

        $this->assertContains('##teamcity[', $result);
        $this->assertFalse($adapter->ok($result), $process->getErrorOutput());
    }

    public function testAdapterDetectsTestsFailingFromError()
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceList'    => $this->root,
            'getTestRunDirectory'      => $this->root,
            'getBaseDirectory'      => $this->root,
            'getTimeout'            => 1200,
            'getTempDirectory'     => $this->tmpDir,
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'ErrorTest'
        ]);

        $adapter = new Phpunit;
        $process = $adapter->getProcess(
            $container,
            true,
            true
        );
        $process->run();

        $result = $process->getOutput();

        $this->assertContains('##teamcity[', $result);
        $this->assertFalse($adapter->ok($result), $process->getErrorOutput());
    }

    public function testAdapterOutputProcessingDetectsFailOverMultipleLinesWithNoDepOnFinalStatusReport()
    {
        $this->markTestIncomplete('This seems redundant as it should never happen - fail on first failure is set');
        $adapter = new Phpunit;
        $output = <<<OUTPUT
TAP version 13
not ok 1 - Error: Humbug\Adapter\PhpunitTest::testAdapterRunsDefaultPhpunitCommand
ok 78 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testReturnsTokenEquivalentToLessThanOrEqualTo
ok 79 - Humbug\Test\Mutator\ConditionalBoundary\LessThanOrEqualToTest::testMutatesLessThanToLessThanOrEqualTo
ok 80 - Humbug\Test\Mutator\ConditionalBoundary\LessThanTest::testReturnsTokenEquivalentToLessThanOrEqualTo
ok 81 - Humbug\Test\Mutator\ConditionalBoundary\LessThanTest::testMutatesLessThanToLessThanOrEqualTo
not ok 103 - Error: Humbug\Test\Utility\TestTimeAnalyserTest::testAnalysisOfJunitLogFormatShowsLeastTimeTestCaseFirst
1..103

OUTPUT;
        $this->assertFalse($adapter->ok($output));
    }

    /**
     * @dataProvider directoriesList
     */
    public function testShouldNotNotifyRegressionWhileRunningProcess($directory)
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceList'    => $directory,
            'getTestRunDirectory'   => $directory,
            'getBaseDirectory'      => $directory,
            'getTimeout'            => 1200,
            'getTempDirectory'     => $this->tmpDir,
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => ''
        ]);

        $adapter = new Phpunit;
        $process = $adapter->getProcess(
            $container,
            true,
            true
        );
        $process->run();

        $result = $process->getOutput();
      
        $this->assertEquals(2, $adapter->hasOks($result), $process->getErrorOutput());
        $this->assertContains('##teamcity[', $result);
        $this->assertTrue($adapter->ok($result), "Regression output: \n" . $result);
    }

    public function directoriesList()
    {
        return [
            [__DIR__ . '/_files/regression/wildcard-dirs'],
            ['tests/Adapter/_files/regression/wildcard-dirs'],
            [__DIR__ . '/_files/regression/server-argv']
        ];
    }
}
