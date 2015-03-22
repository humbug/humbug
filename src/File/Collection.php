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
use Humbug\Exception\InvalidArgumentException;

class Collection
{

    private $files = [];

    public function __construct(array $import = null)
    {
        if (!is_null($import)) {
            if (!isset($import[0]['name']) || !isset($import[0]['hash'])) {
                throw new InvalidArgumentException(
                    'The imported data passed to constructor does match expected collection'
                );
            }
            $this->files = $import;
        }
    }

    public function addFile($file)
    {
        $this->files[] = [
            'name' => $file,
            'hash' => $this->getSha1($file)
        ];
    }

    public function hasFile($file)
    {
        return false !== array_search(
            $file,
            array_map(function ($data) {return $data['name'];}, $this->files)
        );
    }

    public function getFileHash($file)
    {
        if (!$this->hasFile($file)) {
            throw new RuntimeException('File does not exist: ' . $file);
        }
        $index = array_search(
            $file,
            array_map(function ($data) {return $data['name'];}, $this->files)
        );
        if ($this->files[$index]['name'] !== $file) {
            throw new RuntimeException(
                'Internal error: hash entry located was not for intended file: ' . $file
            );
        }
        return $this->files[$index]['hash'];
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
