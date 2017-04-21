<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant;

use Humbug\Container;
use Humbug\Mutant;

class ProcessBuilder
{
    private $adapter;

    private $container;

    public function __construct(Container $container)
    {
        $this->adapter = $container->getAdapter();
        $this->container = $container;
    }

    /**
     * Creates a new process to run mutant tests
     * @param Mutant $mutant
     * @param int $index Index of the mutable file in test suite
     *
     * @return Process
     */
    public function build(Mutant $mutant, $index)
    {
        $process = $this->adapter->getProcess(
            $this->container,
            false,
            $mutant->getMutation()->getFile(), // file to intercept
            $mutant->getFile(), // mutated file to substitute
            $mutant->getTests()
        );

        return new Process($this->adapter, $mutant, $process, $index);
    }
}
