<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug;

use Humbug\Exception\InvalidArgumentException;
use Symfony\Component\Finder\Finder;

class Container
{
    private $inputOptions;

    protected $cache;

    protected $adapter;

    protected $adapterOptions = [];

    protected $mutables = [];

    protected $generator;

    protected $bootstrap = '';

    protected $timeout = null;

    protected $testRunDirectory;

    protected $baseDirectory;

    protected $srcList;

    public function __construct(array $inputOptions)
    {
        $this->inputOptions = $inputOptions;
        $this->setAdapterOptionsFromString($this->inputOptions['options']);
    }

    /**
     * Retrieve any of the original input options
     *
     * @param string $option
     * @return string
     */
    public function get($option)
    {
        if (!array_key_exists($option, $this->inputOptions)) {
            throw new \InvalidArgumentException('Option "'. $option . ' not exists');
        }

        return $this->inputOptions[$option];
    }

    /**
     * Set the directory from which tests must be run (only if not base)
     *
     * @param string $dir
     */
    public function setTestRunDirectory($dir)
    {
        $this->testRunDirectory = rtrim($dir, ' \\/');
        return $this;
    }

    /**
     * Get the directory from which tests must be run
     *
     * @return string
     */
    public function getTestRunDirectory()
    {
        return $this->testRunDirectory;
    }

    /**
     * Set the base directory.
     *
     * @param string $dir
     */
    public function setBaseDirectory($dir)
    {
        $this->baseDirectory = rtrim($dir, ' \\/');
        return $this;
    }

    /**
     * Get the base directory.
     *
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * Set the base directory.
     *
     * @param string $dir
     */
    public function setSourceList(\stdClass $list)
    {
        $this->srcList = $list;
        return $this;
    }

    /**
     * Get the base directory.
     *
     * @return string
     */
    public function getSourceList()
    {
        return $this->srcList;
    }

    /**
     * Set the cache directory of the project being mutated
     *
     * @param string $dir
     */
    public function setCacheDirectory($dir)
    {
        $dir = rtrim($dir, ' \\/');
        if (!is_dir($dir) || !is_readable($dir)) {
            throw new InvalidArgumentException('Invalid cache directory: "'.$dir.'"');
        }
        $this->cache = $dir;
        return $this;
    }

    /**
     * Get the cache directory of the project being mutated
     *
     * @return string
     */
    public function getCacheDirectory()
    {
        if (is_null($this->cache)) {
            return sys_get_temp_dir();
        }
        return $this->cache;
    }

    /**
     * Options to pass to adapter's underlying command
     *
     * @param string $optionString
     */
    public function setAdapterOptionsFromString($optionString)
    {
        $this->adapterOptions = array_merge(
            $this->adapterOptions,
            explode(' ', $optionString)
        );
        return $this;
    }

    /**
     * Set many options for adapter's underlying cli command
     * @param array|string $options Array or serialized array of options
     * @return self
     */
    public function setAdapterOptions($options)
    {
        if (!is_array($options)) {
            $options = unserialize($options);
        }
        foreach ($options as $value) {
            $this->setAdapterOption($value);
        }
        return $this;
    }

    /**
     * Get a space delimited string of testing tool options
     *
     * @return string
     */
    public function getAdapterOptions()
    {
        return $this->adapterOptions;
    }

    /**
     * Get a test framework adapter. Creates a new one based on the configured
     * adapter name passed on the CLI if not already set.
     *
     * @return \Humbug\Adapter\AdapterAbstract
     */
    public function getAdapter()
    {
        if (is_null($this->adapter)) {
            $name = ucfirst(strtolower($this->get('adapter')));
            $class = '\\Humbug\\Adapter\\' . $name;
            $this->adapter = new $class;
        }
        return $this->adapter;
    }

    /**
     * Set a test framework adapter.
     *
     * @param \Humbug\Adapter\AdapterAbstract $adapter
     */
    public function setAdapter(Adapter\AdapterAbstract $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Generate Mutants!
     *
     * @return array
     */
    public function getMutableFiles(Finder $finder)
    {
        if (empty($this->mutables)) {
            $generator = $this->getGenerator();
            $generator->generate($finder);
            $this->mutables = $generator->getMutables();
        }
        return $this->mutables;
    }

    /**
     * Set a specific Generator of mutations (stuck with a subclass).
     * TODO Add interface
     *
     * @param \Humbug\Generator $generator
     * @return $this
     */
    public function setGenerator(Generator $generator)
    {
        $this->generator = $generator;
        return $this;
    }

    /**
     * Get a specific Generator of mutations.
     *
     * @return \Humbug\Generator
     */
    public function getGenerator()
    {
        if (!isset($this->_generator)) {
            $this->generator = new Generator;
        }
        return $this->generator;
    }

    public function setTimeout($seconds)
    {
        $this->timeout = $seconds;
    }

    /**
     * Routed through Console Input class
     */

    public function getTimeout()
    {
        if (!is_null($this->timeout)) {
            return $this->timeout;
        }
        return $this->get('timeout');
    }

    public function getAdapterConstraints()
    {
        return $this->get('constraints');
    }

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = realpath($bootstrap);
    }

    public function getBootstrap()
    {
        return $this->bootstrap;
    }
}
