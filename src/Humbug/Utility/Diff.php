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

use SebastianBergmann\Diff\Differ;

class Diff
{

    protected static $differ;

    public static function difference(&$from, &$to, $lines = 12)
    {
        if (null === self::$differ) {
            self::$differ = new Differ;
        }
        return self::split(
            self::$differ->diff($from, $to),
            "\n",
            $lines
        );
    }

    protected static function split($string, $needle, $nth)
    {
        $max = strlen($string);
        $n = 0;
        for ($i=0; $i < $max; $i++) {
            if ($string[$i] == $needle) {
                $n++;
                if ($n >= $nth) break;
            }
        }
        return substr($string, 0, $i);
    }
}
