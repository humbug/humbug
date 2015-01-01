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

namespace Humbug\Adapter;

use Mockery as m;
use Humbug\Adapter\Phpunit;

class PhpunitTest extends \PHPUnit_Framework_TestCase
{

    protected $bootstrap = null;

    public function setUp()
    {
        $this->root = dirname(__FILE__) . '/_files';
    }

    public function tearDown()
    {
        if (file_exists(sys_get_temp_dir() . '/humbug.xml')) {
            unlink(sys_get_temp_dir() . '/humbug.xml');
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
            'getSourceDirectory'    => __DIR__ . '/_files/phpunit',
            'getTestDirectory'      => __DIR__ . '/_files/phpunit',
            'getBaseDirectory'      => __DIR__ . '/_files/phpunit',
            'getTimeout'            => 1200,
            'getCacheDirectory'     => sys_get_temp_dir(),
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'MM1_MathTest MathTest.php'
        ]);

        $adapter = new Phpunit;
        $result = $adapter->runTests(
            $container,
            true, 
            true
        );
        $this->assertStringStartsWith(
            \PHPUnit_Runner_Version::getVersionString(),
            $result['output']['stdout']
        );
    }

    public function testAdapterRunsPhpunitCommandWithAlltestsFileTarget()
    {

        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceDirectory'    => __DIR__ . '/_files/phpunit2',
            'getTestDirectory'      => __DIR__ . '/_files/phpunit2',
            'getBaseDirectory'      => __DIR__ . '/_files/phpunit2',
            'getTimeout'            => 1200,
            'getCacheDirectory'     => sys_get_temp_dir(),
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'AllTests.php'
        ]);

        $adapter = new Phpunit;
        $result = $adapter->runTests(
            $container,
            true, 
            true
        );
        $this->assertStringStartsWith(
            \PHPUnit_Runner_Version::getVersionString(),
            $result['output']['stdout']
        );
    }

    public function testAdapterDetectsTestsPassing()
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceDirectory'    => $this->root,
            'getTestDirectory'      => $this->root,
            'getBaseDirectory'      => $this->root,
            'getTimeout'            => 1200,
            'getCacheDirectory'     => sys_get_temp_dir(),
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'PassTest'
        ]);
        
        $adapter = new Phpunit;
        $result = $adapter->runTests(
            $container,
            true, 
            true
        );
        $this->assertTrue($adapter->processOutput($result['output']['stdout']));
    }

    public function testAdapterDetectsTestsFailingFromTestFail()
    {

        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceDirectory'    => $this->root,
            'getTestDirectory'      => $this->root,
            'getBaseDirectory'      => $this->root,
            'getTimeout'            => 1200,
            'getCacheDirectory'     => sys_get_temp_dir(),
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'FailTest'
        ]);

        $adapter = new Phpunit;
        $result = $adapter->runTests(
            $container,
            true, 
            true
        );
        $this->assertFalse($adapter->processOutput($result['output']['stdout']));
    }

    public function testAdapterDetectsTestsFailingFromException()
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceDirectory'    => $this->root,
            'getTestDirectory'      => $this->root,
            'getBaseDirectory'      => $this->root,
            'getTimeout'            => 1200,
            'getCacheDirectory'     => sys_get_temp_dir(),
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'ExceptionTest'
        ]);

        $adapter = new Phpunit;
        $result = $adapter->runTests(
            $container,
            true, 
            true
        );
        $this->assertFalse($adapter->processOutput($result['output']['stdout']));
    }

    public function testAdapterDetectsTestsFailingFromError()
    {
        $container = m::mock('\Humbug\Container');
        $container->shouldReceive([
            'getSourceDirectory'    => $this->root,
            'getTestDirectory'      => $this->root,
            'getBaseDirectory'      => $this->root,
            'getTimeout'            => 1200,
            'getCacheDirectory'     => sys_get_temp_dir(),
            'getAdapterOptions'     => [],
            'getBootstrap'          => '',
            'getAdapterConstraints' => 'ErrorTest'
        ]);

        $adapter = new Phpunit;
        $result = $adapter->runTests(
            $container,
            true, 
            true
        );
        $this->assertFalse($adapter->processOutput($result['output']['stdout']));
    }
    
    public function testAdapterOutputProcessingDetectsFailOverMultipleLinesWithNoDepOnFinalStatusReport()
    {
        $adapter = new Phpunit;
        $output = <<<OUTPUT
PHPUnit 3.4.12 by Sebastian Bergmann.

............................................................ 60 / 300
............................................................ 120 / 300
............................................................ 180 / 300
............................................................ 240 / 300
...........................E................................ 300 / 300

Time: 0 seconds, Memory: 11.00Mb

OK (300 tests, 300 assertions)
OUTPUT;
        $this->assertFalse($adapter->processOutput($output));
    }

}
