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
use Humbug\Exception\InvalidArgumentException;
use Humbug\Exception\LogicException;

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
    public function generate($mutableObject = null)
    {
        $files = $this->getFiles();
        foreach ($files as $file) {
            if (is_null($mutableObject)) {
                $mutable = new Mutable($file);
            } else {
                $mutable = new $mutableObject;
                $mutable->setFilename($file);
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

    /**
     * Set the source directory of the source code to be mutated
     *
     * @param string $sourceDirectory
     */
    public function setSourceDirectory($sourceDirectory)
    {
        if (!file_exists($sourceDirectory)) {
            throw new InvalidArgumentException(
                'Directory does not exist: '.$sourceDirectory
            );
        }
        $this->sourceDirectory = $sourceDirectory;
    }

    /**
     * Get the source directory of the source code to be mutated
     *
     * @return string
     */
    public function getSourceDirectory()
    {
        return $this->sourceDirectory;
    }

    /**
     * Return collated files against which mutations can be generated.
     *
     * @return array
     */
    public function getFiles()
    {
        if (empty($this->files)) {
            if ($this->getSourceDirectory() == '') {
                throw new LogicException('Source directory has not been set');
            }
            $this->collateFiles($this->getSourceDirectory());
        }
        return $this->files;
    }

    /**
     * Collate all files capable of being mutated. For now, this only
     * considers files ending in the PHP extension.
     *
     * @return void
     */
    protected function collateFiles($target)
    {
        $d = dir($target);
        while (false !== ($res = $d->read())) {
            if ($res == '.' || $res == '..') {
                continue;
            }
            $entry = $target . '/' . $res;
            if (is_dir($entry)) {
                $this->collateFiles($entry);
                continue;
            } elseif (!preg_match("/\.php$/", $res)) {
                continue;
            }
            $this->files[] = $entry;
        }
        $d->close();
    }
    
}
