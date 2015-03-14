<?php
/**
 * Class collecting source and file data to track changes over time.
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug;

use Humbug\Exception\RuntimeException;
use Humbug\Exception\InvalidArgumentException;

class FileCollection
{

    private $sources = [];

    private $tests = [];

    public function __construct(array $import = null)
    {
        if (!is_null($import)) {
            if (!isset($import['source_files']) || !isset($import['test_files'])) {
                throw new InvalidArgumentException();
            }
            $this->sources = $import['source_files'];
            $this->tests = $import['test_files'];
        }
    }

    public function addSourceFile($file)
    {
        $this->sources[] = [
            'name' => $file,
            'hash' => $this->getSha1($file)
        ];
    }

    public function addTestFile($file)
    {
        $this->tests[] = [
            'name' => $file,
            'hash' => $this->getSha1($file)
        ];
    }

    public function hasSourceFile($file)
    {
        if ($this->hasFile($this->sources, $file)) {
            return true;
        }
        return false;
    }

    public function hasTestFile($file)
    {
        if ($this->hasFile($this->tests, $file)) {
            return true;
        }
        return false;
    }

    public function getSourceFileHash($file)
    {
        if (!$this->hasFile($this->sources, $file)) {
            throw new RuntimeException();
        }
        return $this->getFileHash($this->sources, $file);
    }

    public function getTestFileHash($file)
    {
        if (!$this->hasFile($this->tests, $file)) {
            throw new RuntimeException();
        }
        return $this->getFileHash($this->tests, $file);
    }

    public function toArray()
    {
        return [
            'source_files' => $this->sources,
            'test_files' => $this->tests
        ];
    }

    private function getSha1($file)
    {
        return sha1_file($file);
    }

    private function hasFile(array $collection, $file)
    {
        return false !== array_search(
            $file,
            array_map(function ($data) {return $data['name'];}, $collection)
        );
    }

    private function getFileHash(array $collection, $file)
    {
        $index = array_search(
            $file,
            array_map(function ($data) {return $data['name'];}, $collection)
        );
        if ($collection[$index]['name'] !== $file) {
            throw new RuntimeException();
        }
        return $collection[$index]['hash'];
    }
}
