<?php

namespace Humbug;

use Symfony\Component\Finder\Finder;
use Traversable;

class MutableIterator implements \IteratorAggregate, \Countable
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var array
     */
    private $mutables;

    /**
     * @param Container $container
     * @param string[] $directories
     * @param string[] $excludes
     */
    public function __construct(Container $container, $directories, $excludes)
    {
        $this->container = $container;
        $this->finder = $this->prepareFinder($directories, $excludes);
    }

    protected function prepareFinder($directories, $excludes)
    {
        $finder = new Finder();
        $finder->files()->name('*.php');

        if ($directories) {
            foreach ($directories as $directory) {
                $finder->in($directory);
            }
        } else {
            $finder->in('.');
        }

        if (isset($excludes)) {
            foreach ($excludes as $exclude) {
                $finder->exclude($exclude);
            }
        }

        return $finder;
    }

    /**
     * @return array
     */
    protected function getMutables()
    {
        if (! $this->mutables) {
            $this->mutables = $this->container->getMutableFiles($this->finder);
        }

        return $this->mutables;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getMutables());
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->getMutables());
    }
}