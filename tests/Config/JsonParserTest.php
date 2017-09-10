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

namespace Humbug\Test\Config;

use Humbug\Config\JsonParser;

class JsonParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var JsonParser
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new JsonParser();
    }

    public function testParseShouldReturnConfig()
    {
        $config = $this->parser->parseFile(__DIR__ . '/../_files/config/');
        $this->assertInstanceOf('stdClass', $config);
    }

    public function testParseShouldReturnDistConfigIfNoOther()
    {
        $config = $this->parser->parseFile(__DIR__ . '/../_files/config3/');
        $this->assertInstanceOf('stdClass', $config);
    }

    /**
     * @expectedException \Humbug\Exception\JsonConfigException
     */
    public function testParsesNonDistFilePreferentially()
    {
        $this->parser->parseFile(__DIR__ . '/../_files/config4/');
    }

    /**
     * @expectedException \Humbug\Exception\JsonConfigException
     * @expectedExceptionMessage Please create a humbug.json(.dist) file.
     */
    public function testShouldRiseExceptionWhenFileNotExists()
    {
        $this->parser->parseFile('it/not/exists/');
    }

    /**
     * @expectedException \Humbug\Exception\JsonConfigException
     * @expectedExceptionMessageRegExp |Error parsing configuration file JSON.*|
     */
    public function testShouldRiseExceptionWhenFileContainsInvalidJson()
    {
        $this->parser->parseFile(__DIR__ . '/../_files/config2/');
    }
}
