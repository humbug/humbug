<?php
/**
 * Class collecting source and file data to track changes over time.
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\File;

use Humbug\Exception\RuntimeException;

class Collection
{
    private $files = [];

    public function __construct(array $import = null)
    {
        if (!is_null($import)) {
            $this->files = $import;
        }
    }

    public function addFile($file)
    {
        if ($this->hasFile($file)) {
            return;
        }
        $this->files[$file] = $this->getSha1($file);
    }

    public function hasFile($file)
    {
        return isset($this->files[$file]);
    }

    public function getFileHash($file)
    {
        if (!$this->hasFile($file)) {
            throw new RuntimeException('File does not exist: ' . $file);
        }
        return $this->files[$file];
    }

    public function toArray()
    {
        return $this->files;
    }

    private function getSha1($file)
    {
        return sha1_file($file);
    }
}
