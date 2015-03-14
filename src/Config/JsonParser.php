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

    const FILE = 'humbug.json';

    const DIST = 'humbug.json.dist';

    public function parseFile($path = '')
    {
        $file = $this->guardFileExists($path);

        $config = json_decode(file_get_contents($file));

        $this->guardDecodeErrors($config);

        return $config;
    }

    /**
     * @param $file
     */
    private function guardFileExists($path)
    {
        if (file_exists($path.self::FILE)) {
            return $path.self::FILE;
        }
        if (!file_exists($path.self::DIST)) {
            throw new JsonConfigException(sprintf(
                'Configuration file does not exist: %s. Please create a humbug.json(.dist) file.',
                $path.self::DIST
            ));
        }
        return $path.self::DIST;
    }

    /**
     * @param $config
     */
    private function guardDecodeErrors($config)
    {
        if (null === $config || json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonConfigException(
                'Error parsing configuration file JSON'
                . (function_exists('json_last_error_msg') ? ': ' . json_last_error_msg() : '')
            );
        }
    }
}
