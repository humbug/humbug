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

class Collector
{

    private $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function collect($file)
    {
        if (empty($file) || !file_exists($file)) {
            return;
        }
        $this->collection->addFile($file);
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function write($path)
    {
        file_put_contents($path, json_encode(
            $this->collection->toArray(),
            JSON_PRETTY_PRINT
        ));
    }
}
