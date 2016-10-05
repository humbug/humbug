<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Adapter;

use Humbug\Exception\InvalidArgumentException;

class Locator
{
    private $workingDir;

    public function __construct($workingDir)
    {
        $this->workingDir = $workingDir;
    }

    public function locate($name)
    {
        // @see https://github.com/symfony/Config/blob/master/FileLocator.php#L83
        if (!empty($name) && ('/' === $name[0]
            || '\\' === $name[0]
            || (strlen($name) > 3 && ctype_alpha($name[0]) && $name[1] == ':' && ($name[2] == '\\' || $name[2] == '/'))
        )) {
            if (!file_exists($name)) {
                throw new InvalidArgumentException("$name does not exist");
            }

            return realpath($name);
        }

        $relativePath = $this->workingDir.DIRECTORY_SEPARATOR.$name;
        $glob = glob($relativePath);
        if (file_exists($relativePath) || !empty($glob)) {
            return realpath($relativePath);
        }

        throw new InvalidArgumentException("Could not find file $name working from $this->workingDir");
    }

    public function locateDirectories($name)
    {
        $glob = glob($this->workingDir . '/' . $name, GLOB_ONLYDIR);

        return array_map([$this, 'locate'], $glob);
    }
}
