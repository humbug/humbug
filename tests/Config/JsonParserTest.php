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
use Humbug\Exception\JsonConfigException;

class JsonParserTest extends \PHPUnit_Framework_TestCase
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

    public function testParsesNonDistFilePreferentially()
    {
        $this->expectException(JsonConfigException::class);
        $config = $this->parser->parseFile(__DIR__ . '/../_files/config4/');
    }

    public function testShouldRiseExceptionWhenFileNotExists()
    {
        $this->expectException(JsonConfigException::class);
        $this->expectExceptionMessage('Please create a humbug.json(.dist) file.');
        $this->parser->parseFile('it/not/exists/');
    }

    public function testShouldRiseExceptionWhenFileContainsInvalidJson()
    {
        $this->expectException(JsonConfigException::class);
        $this->expectExceptionMessageRegExp('/Error parsing configuration file JSON.*/');

        $this->parser->parseFile(__DIR__ . '/../_files/config2/');
    }
}
