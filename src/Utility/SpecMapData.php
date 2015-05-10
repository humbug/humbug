<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Utility;

use Humbug\Exception\InvalidArgumentException;
use Humbug\Exception\RuntimeException;
use Humbug\Exception\NoCoveringTestsException;
use Symfony\Component\Finder\Finder;

class SpecMapData
{

    protected $data;

    /**
     * The constructor processes the main coverage report into
     * a set of split files. A coverage data extract per source code file
     * available.
     */
    public function __construct($file)
    {
        if (!$path = realpath($file)) {
            throw new InvalidArgumentException(
                'File does not exist: ' . $file
            );
        }
        $this->data = json_decode(file_get_contents($file), true);
    }

    public function getSpecTitles($file)
    {
        if (!isset($this->data[$file])) {
            throw new NoCoveringTestsException(
                'No specs associated with this source file: ' . $file
            );
        }
        return [$this->data[$file]['specTitle']];
    }
}
