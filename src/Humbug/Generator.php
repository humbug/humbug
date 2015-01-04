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

use Humbug\Mutable;
use Symfony\Component\Finder\Finder;

class Generator
{

    /**
     * Collated files against which mutations can be generated
     *
     * @var array
     */
    protected $files = [];

    /**
     * Path to the source directory of the project being mutated
     *
     * @var string
     */
    protected $sourceDirectory = ''; // move set/check to constructor...

    /**
     * The collection of possible mutations stored as sets of mutation
     * instructions (allowing us to apply and reverse mutations on the fly)
     *
     * @var \Humbug\Mutable[]
     */
    protected $mutables = [];

    /**
     * Given a source directory (@see \Humbug\Generator::setSourceDirectory)
     * pass each to a \Humbug\Mutable instance which is used to generate
     * mutations and store the instructions for applying and reversing them as
     * a set of mutables (instances of \Humbug\Mutation).
     *
     * @return void
     */
    public function generate(Finder $finder, $mutableObject = null)
    {
        foreach ($finder as $file) {
            if (is_null($mutableObject)) {
                $mutable = new Mutable($file->getRealpath());
            } else {
                $mutable = new $mutableObject;
                $mutable->setFilename($file->getRealpath());
            }
            $this->mutables[] = $mutable;
        }
    }

    /**
     * Return an array of mutable files.
     * 
     * @return \Humbug\Mutable[]
     */
    public function getMutables()
    {
        return $this->mutables;
    }
    
}
