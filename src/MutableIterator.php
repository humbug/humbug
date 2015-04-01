<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
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
    private $mutables = null;

    /**
     * @param Container $container
     * @param Finder $finder
     */
    public function __construct(Container $container, Finder $finder)
    {
        $this->container = $container;
        $this->finder = $finder;
    }

    /**
     * @return array
     */
    protected function getMutables()
    {
        if ($this->mutables === null) {
            $this->mutables = $this->container->getMutableFiles($this->finder);
        }

        return $this->mutables;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getMutables());
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->getMutables());
    }
}
