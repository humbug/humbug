<?php

namespace Humbug;

/**
 * Mutation to apply to a source file
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
class Mutation
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $index;

    /**
     * @var string
     */
    private $mutator;

    /**
     * @param string $file
     * @param int $line
     * @param string $class
     * @param string $method
     * @param int $index
     * @param string $mutator
     */
    public function __construct($file, $line, $class, $method, $index, $mutator)
    {
        $this->file = $file;
        $this->line = $line;
        $this->class = $class;
        $this->method = $method;
        $this->index = $index;
        $this->mutator = $mutator;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getMutator()
    {
        return $this->mutator;
    }
}
