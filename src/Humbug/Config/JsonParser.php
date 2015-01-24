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

namespace Humbug\Config;

use Humbug\Config;
use Humbug\Exception\JsonConfigException;

class JsonParser
{
    public function parseFile($file)
    {
        if (!file_exists($file)) {
            throw new JsonConfigException(
                'Configuration file does not exist. Please create a humbug.json file.'
            );
        }

        $config = json_decode(file_get_contents($file));

        if (null === $config || json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonConfigException(
                'Error parsing configuration file JSON'
                . (function_exists('json_last_error_msg') ? ': ' . json_last_error_msg() : '')
            );
        }

        return $config;
    }
}
