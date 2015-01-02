<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Config;

use Humbug\Exception\JsonConfigException;

class Json
{

    protected $json;

    public function __construct($file)
    {
        $this->json = json_decode(file_get_contents($file));
        if (!isset($this->json->basedir)) {
            throw new JsonConfigException('Base directory is not configured');
        }
        $this->json->basedir = realpath($this->json->basedir);
        if (!isset($this->json->srcdir)) {
            throw new JsonConfigException('Source directory is not configured');
        }
        $this->json->srcdir = realpath($this->json->srcdir);
        if (isset($this->json->testdir)) {
            $this->json->testdir = realpath($this->json->testdir);
        }
    }

    public function get($name)
    {
        return isset($this->json->{$name})? $this->json->{$name} : null;
    }


}