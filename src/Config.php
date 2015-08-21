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

namespace Humbug;

use Humbug\Exception\JsonConfigException;

class Config
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getSource()
    {
        if (!isset($this->config->source)) {
            throw new JsonConfigException(
                'Source code data is not included in configuration file'
            );
        }

        if (!isset($this->config->source->directories) && !isset($this->config->source->excludes)) {
            throw new JsonConfigException(
                'You must set at least one source directory or exclude in the configuration file'
            );
        }

        return $this->config->source;
    }

    public function getPhpunitConfig()
    {
        if (!isset($this->config->phpunit)) {
            throw new JsonConfigException(
                'Phpunit destination is not included in configuration file'
            );
        }

        if (!isset($this->config->phpunit->phar)) {
            throw new JsonConfigException(
                'Name of phpunit executable is not included in configuration file'
            );
        }

        if (!isset($this->config->phpunit->path)) {
            throw new JsonConfigException(
                'Path to phpunit executable is not included in configuration file'
            );
        }

        return $this->config->phpunit;
    }

    /**
     * @return bool
     */
    public function isPhpunitConfigured() {
        return isset($this->config->phpunit);
    }

    public function getTimeout()
    {
        if (!isset($this->config->timeout)) {
            return null;
        }

        return $this->config->timeout;
    }

    public function getChDir()
    {
        if (!isset($this->config->chdir)) {
            return null;
        }

        if (!file_exists($this->config->chdir)) {
            throw new JsonConfigException(
                'Directory in which to run tests does not exist: ' . $this->config->chdir
            );
        }

        return $this->config->chdir;
    }

    public function getLogsJson()
    {
        return $this->getLogs('json');
    }

    public function getLogsText()
    {
        return $this->getLogs('text');
    }

    private function getLogs($type)
    {
        if (!isset($this->config->logs->{$type})) {
            return null;
        }

        if (!is_dir(dirname($this->config->logs->{$type})) && !mkdir(dirname($this->config->logs->{$type}), 0755, true)) {
            throw new JsonConfigException(
                'Directory for ' . $type . ' logging does not exist and could not be created: '
                . dirname($this->config->logs->{$type})
            );
        }

        return $this->config->logs->{$type};
    }
}
