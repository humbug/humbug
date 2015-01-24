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
        $config = $this->parser->parseFile(__DIR__ . '/../_files/config/humbug.json');

        $this->assertInstanceOf('stdClass', $config);
    }

    public function testShouldRiseExceptionWhenFileNotExists()
    {
        $this->setExpectedException(
            '\Humbug\Exception\JsonConfigException',
            'Configuration file does not exist. Please create a humbug.json file.'
        );

        $this->parser->parseFile('it/not/exists');
    }

    public function testShouldRiseExceptionWhenFileContainsInvalidJson()
    {
        $this->setExpectedExceptionRegExp(
            '\Humbug\Exception\JsonConfigException',
            '/Error parsing configuration file JSON.*/'
            );

        $this->parser->parseFile(__DIR__ . '/../_files/config/invalid.json');
    }
}