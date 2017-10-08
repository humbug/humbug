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

namespace Humbug\Test;

use Humbug\Container;
use Humbug\Exception\InvalidArgumentException;
use Mockery as m;
use Symfony\Component\Finder\Finder;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    private $container;

    public function setup()
    {
        $this->container = new Container(['timeout'=>10]);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testShouldHaveAdapterOptionsAfterCreate()
    {
        $input = [
            'options' => 'adapterOpt1 adapterOpt2'
        ];
        $container = new Container($input);
        $this->assertSame(['adapterOpt1', 'adapterOpt2'], $container->getAdapterOptions());
    }

    public function testGetShouldReturnInputOption()
    {
        $input = [
            'options' => 'adapterOpt1 adapterOpt2',
            'test' => 'test-option'
        ];
        $container = new Container($input);
        $this->assertSame('test-option', $container->get('test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetShouldRiseExceptionForUnknownOption()
    {
        $input = [
            'options' => null
        ];

        $container = new Container($input);
        $container->get('invalid-option');
    }

    public function testSetRunDirectory()
    {
        $result = $this->container->setTestRunDirectory('/tests/');
        $this->assertEquals('/tests', $this->container->getTestRunDirectory());
        $this->assertSame($this->container, $result);
    }

    public function testSetBaseDirectory()
    {
        $result = $this->container->setBaseDirectory('/test/');
        $this->assertEquals('/test', $this->container->getBaseDirectory());
        $this->assertSame($this->container, $result);
    }

    public function testSetSrcList()
    {
        $list = new \stdClass;
        $list->foo = 'bar';
        $result = $this->container->setSourceList($list);
        $this->assertEquals('bar', $this->container->getSourceList()->foo);
        $this->assertSame($this->container, $result);
    }

    public function testsetTempDirectory()
    {
        $tmp = sys_get_temp_dir();
        $result = $this->container->setTempDirectory($tmp.'/');
        $this->assertEquals($tmp, $this->container->getTempDirectory());
        $this->assertSame($this->container, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testsetTempDirectoryThrowsExceptionOnUnwriteableParam()
    {
        $result = $this->container->setTempDirectory('/really/does/not/exist');
    }

    public function testSetPrimaryTimeout()
    {
        $this->assertEquals(10, $this->container->getTimeout());
        $result = $this->container->setTimeout(20);
        $this->assertEquals(20, $this->container->getTimeout());
        $this->assertSame($this->container, $result);
    }

    public function testSetBootstrap()
    {
        $tmp = tempnam(sys_get_temp_dir(), uniqid());
        $this->container->setBootstrap($tmp);
        $this->assertEquals($tmp, $this->container->getBootstrap());
        @unlink($tmp);
    }

    public function testGettingMutableFiles()
    {
        $expected = ['/mutate/me.php'];
        $generator = m::mock('Humbug\\Generator');
        $finder = m::mock('Symfony\\Component\\Finder\\Finder');
        $generator->shouldReceive('generate')->with($finder)->andReturn(null);
        $generator->shouldReceive('getMutables')->andReturn($expected);
        $result = $this->container->setGenerator($generator);
        $this->assertSame($this->container, $result);
        $result2 = $this->container->getMutableFiles($finder);
        $this->assertSame($expected, $result2);
    }
}
